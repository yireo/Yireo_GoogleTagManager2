<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Dispatch;

use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\DispatchMode;

class DispatcherResolver
{
    private Config $config;
    private CronDispatcher $cronDispatcher;
    private QueueDispatcher $queueDispatcher;

    public function __construct(
        Config $config,
        CronDispatcher $cronDispatcher,
        QueueDispatcher $queueDispatcher
    ) {
        $this->config = $config;
        $this->cronDispatcher = $cronDispatcher;
        $this->queueDispatcher = $queueDispatcher;
    }

    public function resolve(): DispatcherInterface
    {
        if ($this->config->getDispatchMode() === DispatchMode::QUEUE) {
            return $this->queueDispatcher;
        }

        return $this->cronDispatcher;
    }
}
