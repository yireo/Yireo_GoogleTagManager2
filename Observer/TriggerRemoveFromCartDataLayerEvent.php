<?php declare(strict_types=1);

namespace AdPage\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use AdPage\GTM\Api\CheckoutSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\RemoveFromCart as RemoveFromCartEvent;

class TriggerRemoveFromCartDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private RemoveFromCartEvent $removeFromCartEvent;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        RemoveFromCartEvent $removeFromCartEvent
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->removeFromCartEvent = $removeFromCartEvent;
    }

    public function execute(Observer $observer)
    {
        /** @var CartItemInterface $quoteItem */
        $quoteItem = $observer->getData('quote_item');
        $this->checkoutSessionDataProvider->add(
            'remove_from_cart_event',
            $this->removeFromCartEvent->setCartItem($quoteItem)->get()
        );
    }
}
