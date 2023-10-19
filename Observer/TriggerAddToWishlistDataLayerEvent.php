<?php declare(strict_types=1);

namespace AdPage\GTM\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use AdPage\GTM\Api\CustomerSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\AddToWishlist as AddToWishlistEvent;

class TriggerAddToWishlistDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private AddToWishlistEvent $addToWishlistEvent;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        AddToWishlistEvent $addToWishlistEvent
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->addToWishlistEvent = $addToWishlistEvent;
    }

    public function execute(Observer $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getData('product');
        $this->customerSessionDataProvider->add(
            'add_to_wishlist_event',
            $this->addToWishlistEvent->setProduct($product)->get()
        );
    }
}
