<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToCart as AddToCartEvent;

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
        $this->checkoutSessionDataProvider->add(
            'add_to_cart_event',
            $this->addToCartEvent->setProduct($product)->get()
        );
    }
}
