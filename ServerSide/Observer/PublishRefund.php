<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishRefund implements ObserverInterface
{
    private OrderEventPublisher $orderEventPublisher;
    private Debugger $debugger;

    public function __construct(
        OrderEventPublisher $orderEventPublisher,
        Debugger $debugger
    ) {
        $this->orderEventPublisher = $orderEventPublisher;
        $this->debugger = $debugger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->doExecute($observer);
        } catch (Throwable $exception) {
            $this->debugger->debug('Server-side observer ' . self::class . ' failed', $exception->getMessage());
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
