<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\PurchaseTrigger;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishPurchaseOnOrderState implements ObserverInterface
{
    private OrderEventPublisher $orderEventPublisher;
    private Config $config;
    private LoggerInterface $logger;

    public function __construct(
        OrderEventPublisher $orderEventPublisher,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->orderEventPublisher = $orderEventPublisher;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->doExecute($observer);
        } catch (Throwable $exception) {
            $this->logger->error(
                'Server-side tracking observer ' . self::class . ' failed: ' . $exception->getMessage()
            );
        }
    }

    private function doExecute(Observer $observer): void
    {
        $order = $observer->getEvent()->getData('order');
        if (false === $order instanceof OrderInterface) {
            return;
        }

        $storeId = (int)$order->getStoreId();
        if ($this->config->getPurchaseTrigger($storeId) !== PurchaseTrigger::ORDER_STATE) {
            return;
        }

        if (false === in_array($order->getState(), $this->config->getPurchaseOrderStates($storeId), true)) {
            return;
        }

        $this->orderEventPublisher->publishPurchase($order);
    }
}
