<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToWishlist as AddToWishlistEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerAddToWishlistDataLayerEvent implements ObserverInterface
{
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private AddToWishlistEvent $addToWishlistEvent;
    private LoggerInterface $logger;

    public function __construct(
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        AddToWishlistEvent $addToWishlistEvent,
        LoggerInterface $logger
    ) {
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->addToWishlistEvent = $addToWishlistEvent;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->doExecute($observer);
        } catch (Throwable $exception) {
            $this->logger->error(
                'GTM data layer observer ' . self::class . ' failed: ' . $exception->getMessage()
            );
        }
    }

    private function doExecute(Observer $observer): void
    {
        /** @var ProductInterface $product */
        $product = $observer->getData('product');
        $this->customerSessionDataProvider->add(
            'add_to_wishlist_event',
            $this->addToWishlistEvent->setProduct($product)->get()
        );
    }
}
