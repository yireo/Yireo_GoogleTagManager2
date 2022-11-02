<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToWishlist as AddToWishlistEvent;

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
        $this->customerSessionDataProvider->append($this->addToWishlistEvent->get($product));
    }
}
