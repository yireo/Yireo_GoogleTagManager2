<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Cron;

use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\DispatchMode;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextRepository;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;
use Yireo\GoogleTagManager2\ServerSide\Processor;

class Outbox
{
    private const CONTEXT_RETENTION_DAYS = 90;

    private Processor $processor;
    private Config $config;
    private OutboxRepository $outboxRepository;
    private ContextRepository $contextRepository;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Processor $processor,
        Config $config,
        OutboxRepository $outboxRepository,
        ContextRepository $contextRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->processor = $processor;
        $this->config = $config;
        $this->outboxRepository = $outboxRepository;
        $this->contextRepository = $contextRepository;
        $this->storeManager = $storeManager;
    }

    public function processPending(): void
    {
        if ($this->config->getDispatchMode() === DispatchMode::QUEUE || false === $this->isEnabledForAnyStore()) {
            return;
        }

        $this->processor->processPending();
    }

    public function retryFailed(): void
    {
        if (false === $this->isEnabledForAnyStore()) {
            return;
        }

        $this->processor->retryFailed();
    }

    public function cleanup(): void
    {
        $this->outboxRepository->cleanup($this->config->getRetentionDays());
        $this->contextRepository->cleanup(max(self::CONTEXT_RETENTION_DAYS, $this->config->getRetentionDays()));
    }

    private function isEnabledForAnyStore(): bool
    {
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->config->isEnabled((int)$store->getId())) {
                return true;
            }
        }

        return false;
    }
}
