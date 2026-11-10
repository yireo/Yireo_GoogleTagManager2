<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Dispatch;

use Magento\Framework\MessageQueue\PublisherInterface;

class QueueDispatcher implements DispatcherInterface
{
    public const TOPIC = 'yireo_gtm2.serverside.event';

    private PublisherInterface $publisher;

    public function __construct(
        PublisherInterface $publisher
    ) {
        $this->publisher = $publisher;
    }

    public function dispatch(int $outboxId): void
    {
        $this->publisher->publish(self::TOPIC, (string)$outboxId);
    }
}
