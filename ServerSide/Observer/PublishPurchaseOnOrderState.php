<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\PurchaseTrigger;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishPurchaseOnOrderState implements ObserverInterface
{
    private OrderEventPublisher $orderEventPublisher;
    private Config $config;
    private Debugger $debugger;

    public function __construct(
        OrderEventPublisher $orderEventPublisher,
        Config $config,
        Debugger $debugger
    ) {
        $this->orderEventPublisher = $orderEventPublisher;
        $this->config = $config;
        $this->debugger = $debugger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->doExecute($observer);
        } catch (Throwable $exception) {
            $this->debugger->debug('Server-side observer ' . self::class . ' failed', $exception->getMessage());
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
