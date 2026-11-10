<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\PurchaseTrigger;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishPurchaseOnInvoicePay implements ObserverInterface
{
    private OrderEventPublisher $orderEventPublisher;
    private Config $config;

    public function __construct(
        OrderEventPublisher $orderEventPublisher,
        Config $config
    ) {
        $this->orderEventPublisher = $orderEventPublisher;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getData('invoice');
        if (false === $invoice instanceof Invoice) {
            return;
        }

        $order = $invoice->getOrder();
        if ($this->config->getPurchaseTrigger((int)$order->getStoreId()) !== PurchaseTrigger::INVOICE_PAY) {
            return;
        }

        $this->orderEventPublisher->publishPurchase($order);
    }
}
