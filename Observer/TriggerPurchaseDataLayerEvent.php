<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\Purchase as PurchaseEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerPurchaseDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private PurchaseEvent $purchaseEvent;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        PurchaseEvent $purchaseEvent,
        LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->purchaseEvent = $purchaseEvent;
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
        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $purchaseEventData = $this->purchaseEvent->setOrder($order)->get();
        if (empty($purchaseEventData)) {
            return;
        }

        $this->checkoutSessionDataProvider->add(
            'purchase_event',
            $purchaseEventData
        );
    }
}
