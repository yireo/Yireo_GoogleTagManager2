<?php

declare(strict_types=1);

namespace Tagging\GTM\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Tagging\GTM\DataLayer\Event\PurchaseWebhookEvent;
use Psr\Log\LoggerInterface;
use Exception;
use Tagging\GTM\Logger\Debugger;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;

class TriggerPurchaseWebhookEvent implements ObserverInterface
{
    private PurchaseWebhookEvent $webhookEvent;
    private Debugger $debugger;
    private OrderPaymentRepositoryInterface $orderPaymentRepository;
    
    public function __construct(
        PurchaseWebhookEvent $webhookEvent,
        Debugger $debugger,
        OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {
        $this->webhookEvent = $webhookEvent;
        $this->debugger = $debugger;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getOrder();

        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): has been triggered');
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_id: ' . $order->getId());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_increment_id: ' . $order->getIncrementId());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_status: ' . $order->getStatus());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_state: ' . $order->getState());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_grand_total: ' . $order->getGrandTotal());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_total_paid: ' . $order->getTotalPaid());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_total_due: ' . $order->getTotalDue());
        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): order_payment_method: ' . $order->getPayment()->getMethod());

        $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): data_has_changed_for_total_paid: ' .
            ($order->dataHasChangedFor('total_paid') ? 'true' : 'false'));

        // Improved trigger logic with better tolerance and fallback conditions
        if (!$this->shouldTriggerWebhook($order)) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): webhook trigger conditions not met, skipping');
            return;
        }

        try {
            $payment = $order->getPayment();
            $saveKey = 'trytagging_webhook_processed';
            $failedAttemptsKey = 'trytagging_webhook_failed_attempts';

            $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): trigger process_status: ' . $payment->getAdditionalInformation($saveKey));

            if ($payment->getAdditionalInformation($saveKey)) {
                return;
            }

            $success = $this->webhookEvent->purchase($order);
            
            if ($success) {
                $payment->setAdditionalInformation($saveKey, true);
                $payment->setAdditionalInformation($failedAttemptsKey, null); // Clear failed attempts
                $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): webhook sent successfully');
            } else {
                // Track failed attempts for potential retry mechanism
                $failedAttempts = (int)$payment->getAdditionalInformation($failedAttemptsKey) + 1;
                $payment->setAdditionalInformation($failedAttemptsKey, $failedAttempts);
                $payment->setAdditionalInformation('trytagging_webhook_last_attempt', time());
                $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): webhook failed, attempt #' . $failedAttempts);
            }
            
            $this->orderPaymentRepository->save($payment);
        } catch (\Exception $e) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::execute(): error: ' . $e->getMessage());
        }
    }

    /**
     * Improved logic to determine if webhook should be triggered
     * More robust than the original early return logic
     */
    private function shouldTriggerWebhook(OrderInterface $order): bool
    {
        $grandTotal = (float)$order->getGrandTotal();
        $totalPaid = (float)$order->getTotalPaid();
        $tolerance = 0.01; // Tolerance for floating point comparison

        // Log the comparison for debugging
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): grand_total: ' . $grandTotal);
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): total_paid: ' . $totalPaid);
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): difference: ' . abs($grandTotal - $totalPaid));

        // Primary condition: data has changed for total_paid AND order is fully paid (with tolerance)
        if ($order->dataHasChangedFor('total_paid') && abs($grandTotal - $totalPaid) <= $tolerance) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): primary condition met');
            return true;
        }

        // Fallback condition 1: Order is in paid state regardless of data changes
        if (in_array($order->getState(), ['processing', 'complete']) && abs($grandTotal - $totalPaid) <= $tolerance) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): fallback condition 1 met (order in paid state)');
            return true;
        }

        // Fallback condition 2: Total paid is close to grand total (handles partial payments completing)
        if ($totalPaid > 0 && abs($grandTotal - $totalPaid) <= $tolerance) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): fallback condition 2 met (payment complete)');
            return true;
        }

        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): no conditions met');
        return false;
    }
}
