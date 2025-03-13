<?php declare(strict_types=1);

namespace Tagging\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Tagging\GTM\DataLayer\Event\PurchaseWebhookEvent;
use Psr\Log\LoggerInterface;
use Exception;
use Tagging\GTM\Logger\Debugger;
class TriggerPurchaseWebhookEvent implements ObserverInterface
{
    private LoggerInterface $logger;
    private PurchaseWebhookEvent $webhookEvent;
    private Debugger $debugger;

    public function __construct(
        PurchaseWebhookEvent $webhookEvent,
        LoggerInterface $logger,
        Debugger $debugger
    ) {
        $this->logger = $logger;
        $this->webhookEvent = $webhookEvent;
        $this->debugger = $debugger;
    }

    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        /** @var OrderInterface $order */
        $order = $invoice->getOrder();

        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): has been triggered');
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_id: ' . $order->getId());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_increment_id: ' . $order->getIncrementId());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_status: ' . $order->getStatus());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_state: ' . $order->getState());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_grand_total: ' . $order->getGrandTotal());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_total_paid: ' . $order->getTotalPaid());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_total_due: ' . $order->getTotalDue());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_payment_method: ' . $order->getPayment()->getMethod());
        
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): invoice_id: ' . $invoice->getId());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): invoice_increment_id: ' . $invoice->getIncrementId());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): invoice_state: ' . $invoice->getState());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): invoice_grand_total: ' . $invoice->getGrandTotal());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): invoice_total_paid: ' . $invoice->getGrandTotal());
        
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): data_has_changed_for_total_paid: ' . 
            ($order->dataHasChangedFor('total_paid') ? 'true' : 'false'));
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): grand_total_greater_than_total_paid: ' . 
            ($order->getGrandTotal() > $order->getTotalPaid() ? 'true' : 'false'));

        if (!$order->dataHasChangedFor('total_paid') || $order->getGrandTotal() > $order->getTotalPaid()) {
            return;
        }

        try {
            $this->webhookEvent->purchase($order);
        } catch (\Exception $e) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): error: ' . $e->getMessage());
        }
    }
}
