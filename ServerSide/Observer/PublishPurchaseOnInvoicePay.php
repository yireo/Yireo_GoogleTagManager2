<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;
use Psr\Log\LoggerInterface;
use Throwable;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\PurchaseTrigger;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;

class PublishPurchaseOnInvoicePay implements ObserverInterface
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
