<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;

class Processor
{
    private const STALE_MINUTES = 15;

    private OutboxRepository $outboxRepository;
    private Sender $sender;
    private Config $config;
    private Debugger $debugger;

    public function __construct(
        OutboxRepository $outboxRepository,
        Sender $sender,
        Config $config,
        Debugger $debugger
    ) {
        $this->outboxRepository = $outboxRepository;
        $this->sender = $sender;
        $this->config = $config;
        $this->debugger = $debugger;
    }

    public function processById(int $id): bool
    {
        if (false === $this->outboxRepository->claim($id, $this->config->getMaxAttempts())) {
            return false;
        }

        $row = $this->outboxRepository->getById($id);
        if ($row === null) {
            return false;
        }

        try {
            $result = $this->sender->send((int)$row['store_id'], (string)$row['payload']);
        } catch (Throwable $exception) {
            $this->outboxRepository->markFailed($id, $exception->getMessage());
            $this->debugger->debug('Server-side event ' . $id . ' failed', $exception->getMessage());
            return false;
        }

        if ($result->isSuccess()) {
            $this->outboxRepository->markSent($id);
            return true;
        }

        $this->outboxRepository->markFailed($id, 'HTTP ' . $result->getStatus() . ': ' . $result->getBody());
        return false;
    }

    public function processPending(int $limit = 100): int
    {
        return $this->processStatus(OutboxRepository::STATUS_PENDING, $limit);
    }

    public function retryFailed(int $limit = 100): int
    {
        $this->outboxRepository->releaseStale(self::STALE_MINUTES);
        $sent = $this->processStatus(OutboxRepository::STATUS_FAILED, $limit);
        return $sent + $this->processStatus(OutboxRepository::STATUS_PENDING, $limit, self::STALE_MINUTES);
    }

    private function processStatus(string $status, int $limit, int $olderThanMinutes = 0): int
    {
        $sent = 0;
        $maxAttempts = $this->config->getMaxAttempts();
        foreach ($this->outboxRepository->getIdsByStatus($status, $maxAttempts, $limit, $olderThanMinutes) as $id) {
            if ($this->processById($id)) {
                $sent++;
            }
        }

        return $sent;
    }
}
