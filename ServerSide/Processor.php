<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;

class Processor
{
    private const STALE_MINUTES = 15;
    private const TIME_BUDGET = 50;
    private const MAX_CONNECTION_FAILURES = 3;

    private OutboxRepository $outboxRepository;
    private Sender $sender;
    private Config $config;
    private Debugger $debugger;
    private float $deadline = 0;
    private int $connectionFailures = 0;

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
        $result = $this->process($id);

        return $result !== null && $result->isSuccess();
    }

    public function processPending(int $limit = 100): int
    {
        $this->startRun();

        return $this->processStatus(OutboxRepository::STATUS_PENDING, $limit);
    }

    public function retryFailed(int $limit = 100): int
    {
        $this->startRun();
        $this->outboxRepository->releaseStale(self::STALE_MINUTES);
        $sent = $this->processStatus(OutboxRepository::STATUS_FAILED, $limit);

        return $sent + $this->processStatus(OutboxRepository::STATUS_PENDING, $limit, self::STALE_MINUTES);
    }

    private function process(int $id): ?SendResult
    {
        if (false === $this->outboxRepository->claim($id, $this->config->getMaxAttempts())) {
            return null;
        }

        $row = $this->outboxRepository->getById($id);
        if ($row === null) {
            return null;
        }

        try {
            $result = $this->sender->send((int)$row['store_id'], (string)$row['payload']);
        } catch (Throwable $exception) {
            $this->outboxRepository->markFailed($id, $exception->getMessage());
            $this->debugger->debug('Server-side event ' . $id . ' failed', $exception->getMessage());
            return new SendResult(false, 0, $exception->getMessage());
        }

        if ($result->isSuccess()) {
            $this->outboxRepository->markSent($id);
            return $result;
        }

        $this->outboxRepository->markFailed($id, 'HTTP ' . $result->getStatus() . ': ' . $result->getBody());
        return $result;
    }

    private function startRun(): void
    {
        $this->deadline = microtime(true) + self::TIME_BUDGET;
        $this->connectionFailures = 0;
    }

    private function processStatus(string $status, int $limit, int $olderThanMinutes = 0): int
    {
        $sent = 0;
        $maxAttempts = $this->config->getMaxAttempts();
        foreach ($this->outboxRepository->getIdsByStatus($status, $maxAttempts, $limit, $olderThanMinutes) as $id) {
            if ($this->shouldStop()) {
                break;
            }

            $result = $this->process($id);
            if ($result === null) {
                continue;
            }

            if ($result->isSuccess()) {
                $this->connectionFailures = 0;
                $sent++;
                continue;
            }

            $this->connectionFailures = $result->getStatus() === 0 ? $this->connectionFailures + 1 : 0;
        }

        return $sent;
    }

    private function shouldStop(): bool
    {
        if ($this->connectionFailures >= self::MAX_CONNECTION_FAILURES) {
            $this->debugger->debug('Server-side processing stopped after repeated connection failures');
            return true;
        }

        if (microtime(true) >= $this->deadline) {
            $this->debugger->debug('Server-side processing stopped after ' . self::TIME_BUDGET . ' seconds');
            return true;
        }

        return false;
    }
}
