<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\RemoveFromCart as RemoveFromCartEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerRemoveFromCartDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private RemoveFromCartEvent $removeFromCartEvent;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        RemoveFromCartEvent $removeFromCartEvent,
        LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->removeFromCartEvent = $removeFromCartEvent;
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
        /** @var CartItemInterface $quoteItem */
        $quoteItem = $observer->getData('quote_item');
        $this->checkoutSessionDataProvider->add(
            'remove_from_cart_event',
            $this->removeFromCartEvent->setCartItem($quoteItem)->get()
        );
    }
}
