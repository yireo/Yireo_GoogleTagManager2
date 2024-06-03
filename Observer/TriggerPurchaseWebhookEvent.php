<?php declare(strict_types=1);

namespace Tagging\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Tagging\GTM\DataLayer\Event\PurchaseWebhookEvent;
use Psr\Log\LoggerInterface;
use Exception;

class TriggerPurchaseWebhookEvent implements ObserverInterface
{
    private LoggerInterface $logger;
    private PurchaseWebhookEvent $webhookEvent;

    public function __construct(
        PurchaseWebhookEvent $webhookEvent,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->webhookEvent = $webhookEvent;
    }

    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        /** @var OrderInterface $order */
        $order = $invoice->getOrder();

        if (!$order->dataHasChangedFor('total_paid') || $order->getGrandTotal() > $order->getTotalPaid()) {
            return;
        }

        try {
            $this->webhookEvent->purchase($order);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
