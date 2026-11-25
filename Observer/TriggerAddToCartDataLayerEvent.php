<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToCart as AddToCartEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerAddToCartDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private AddToCartEvent $addToCartEvent;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        AddToCartEvent $addToCartEvent,
        LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->addToCartEvent = $addToCartEvent;
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
