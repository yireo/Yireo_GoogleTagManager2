<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Psr\Log\LoggerInterface;
use Throwable;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishRefund implements ObserverInterface
{
    private OrderEventPublisher $orderEventPublisher;
    private LoggerInterface $logger;

    public function __construct(
        OrderEventPublisher $orderEventPublisher,
        LoggerInterface $logger
    ) {
        $this->orderEventPublisher = $orderEventPublisher;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->doExecute($observer);
        } catch (Throwable $exception) {
            $this->logger->error(
                'Server-side tracking observer ' . self::class . ' failed: ' . $exception->getMessage()
            );
        }
    }

    private function doExecute(Observer $observer): void
    {
        $creditmemo = $observer->getEvent()->getData('creditmemo');
        if (false === $creditmemo instanceof Creditmemo || !$creditmemo->getEntityId()) {
            return;
        }

        $this->orderEventPublisher->publishRefund($creditmemo, $creditmemo->getOrder());
    }
}
