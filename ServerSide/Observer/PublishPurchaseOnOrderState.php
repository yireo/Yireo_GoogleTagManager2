<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\PurchaseTrigger;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishPurchaseOnOrderState implements ObserverInterface
{
    private OrderEventPublisher $orderEventPublisher;
    private Config $config;

    public function __construct(
        OrderEventPublisher $orderEventPublisher,
        Config $config
    ) {
        $this->orderEventPublisher = $orderEventPublisher;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        if (false === $order instanceof OrderInterface) {
            return;
        }

        $storeId = (int)$order->getStoreId();
        if ($this->config->getPurchaseTrigger($storeId) !== PurchaseTrigger::ORDER_STATE) {
            return;
        }

        if (false === in_array($order->getState(), $this->config->getPurchaseOrderStates($storeId), true)) {
            return;
        }

        $this->orderEventPublisher->publishPurchase($order);
    }
}
