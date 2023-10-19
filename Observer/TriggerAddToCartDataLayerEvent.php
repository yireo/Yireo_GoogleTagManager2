<?php declare(strict_types=1);

namespace AdPage\GTM\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use AdPage\GTM\Api\CheckoutSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\AddToCart as AddToCartEvent;

class TriggerAddToCartDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddToCartEvent $addToCartEvent;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddToCartEvent $addToCartEvent
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addToCartEvent = $addToCartEvent;
    }

    public function execute(Observer $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getData('product');
        $qty = (int)$observer->getData('request')->getParam('qty');
        if ($qty === 0) {
            $qty = 1;
        }

        $this->checkoutSessionDataProvider->add(
            'add_to_cart_event',
            $this->addToCartEvent->setProduct($product)->setQty($qty)->get()
        );
    }
}
