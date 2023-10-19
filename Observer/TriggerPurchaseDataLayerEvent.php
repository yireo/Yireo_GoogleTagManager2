<?php declare(strict_types=1);

namespace AdPage\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use AdPage\GTM\Api\CheckoutSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\Purchase as PurchaseEvent;

class TriggerPurchaseDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private PurchaseEvent $purchaseEvent;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        PurchaseEvent $purchaseEvent
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->purchaseEvent = $purchaseEvent;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $this->checkoutSessionDataProvider->add(
            'purchase_event',
            $this->purchaseEvent->setOrder($order)->get()
        );
    }
}
