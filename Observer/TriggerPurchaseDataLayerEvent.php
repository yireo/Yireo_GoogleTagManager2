<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Event\Purchase as PurchaseEvent;

class TriggerPurchaseDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private PurchaseEvent $purchaseEvent;
    private Config $config;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        PurchaseEvent $purchaseEvent,
        Config $config
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->purchaseEvent = $purchaseEvent;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        if (false === in_array($order->getStatus(), $this->getOrderStates())) {
            return;
        }

        $this->checkoutSessionDataProvider->add(
            'purchase_event',
            $this->purchaseEvent->setOrder($order)->get()
        );
    }

    private function getOrderStates(): array
    {
        $orderStates = $this->config->getOrderStatesForPurchaseEvent();
        if (!empty($orderStates)){
            return $orderStates;
        }

        return $this->getDefaultOrderStates();
    }

    private function getDefaultOrderStates(): array
    {
        return [
            Order::STATE_PENDING_PAYMENT,
            Order::STATE_PAYMENT_REVIEW,
            Order::STATE_HOLDED,
            Order::STATE_PROCESSING,
            Order::STATE_COMPLETE,
        ];
    }
}
