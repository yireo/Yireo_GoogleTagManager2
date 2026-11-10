<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishRefund implements ObserverInterface
{
    private OrderEventPublisher $orderEventPublisher;

    public function __construct(
        OrderEventPublisher $orderEventPublisher
    ) {
        $this->orderEventPublisher = $orderEventPublisher;
    }

    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getData('creditmemo');
        if (false === $creditmemo instanceof Creditmemo || !$creditmemo->getEntityId()) {
            return;
        }

        $this->orderEventPublisher->publishRefund($creditmemo, $creditmemo->getOrder());
    }
}
