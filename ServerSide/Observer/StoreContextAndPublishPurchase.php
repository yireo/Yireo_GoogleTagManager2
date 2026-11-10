<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\PurchaseTrigger;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class StoreContextAndPublishPurchase implements ObserverInterface
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

        $this->orderEventPublisher->storeContext($order);

        if ($this->config->getPurchaseTrigger((int)$order->getStoreId()) === PurchaseTrigger::ORDER_PLACE) {
            $this->orderEventPublisher->publishPurchase($order);
        }
    }
}
