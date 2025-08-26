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
use Tagging\GTM\Config\Config;

class TriggerPurchaseWebhookEvent implements ObserverInterface
{
    private PurchaseWebhookEvent $webhookEvent;
    private Debugger $debugger;
    private OrderPaymentRepositoryInterface $orderPaymentRepository;
    private Config $config;
    
    public function __construct(
        PurchaseWebhookEvent $webhookEvent,
        Debugger $debugger,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        Config $config
    ) {
        $this->webhookEvent = $webhookEvent;
        $this->debugger = $debugger;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->config = $config;
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
     * Supports both default (total paid) and order state trigger modes
     */
    private function shouldTriggerWebhook(OrderInterface $order): bool
    {
        $triggerMode = $this->config->getWebhookTriggerMode();
        
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhook(): trigger_mode: ' . $triggerMode);
        
        // Use order state trigger mode if configured
        if ($this->config->isWebhookTriggerOnOrderState()) {
            return $this->shouldTriggerWebhookOnOrderState($order);
        }
        
        // Default behavior: use total paid logic (backwards compatible)
        return $this->shouldTriggerWebhookOnTotalPaid($order);
    }

    /**
     * Default trigger logic based on total paid (backwards compatible)
     */
    private function shouldTriggerWebhookOnTotalPaid(OrderInterface $order): bool
    {
        $grandTotal = (float)$order->getGrandTotal();
        $totalPaid = (float)$order->getTotalPaid();
        $tolerance = 0.01; // Tolerance for floating point comparison

        // Log the comparison for debugging
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnTotalPaid(): grand_total: ' . $grandTotal);
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnTotalPaid(): total_paid: ' . $totalPaid);
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnTotalPaid(): difference: ' . abs($grandTotal - $totalPaid));

        // Primary condition: data has changed for total_paid AND order is fully paid (with tolerance)
        if ($order->dataHasChangedFor('total_paid') && abs($grandTotal - $totalPaid) <= $tolerance) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnTotalPaid(): primary condition met');
            return true;
        }

        // Fallback condition 1: Order is in paid state regardless of data changes
        if (in_array($order->getState(), ['processing', 'complete']) && abs($grandTotal - $totalPaid) <= $tolerance) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnTotalPaid(): fallback condition 1 met (order in paid state)');
            return true;
        }

        // Fallback condition 2: Total paid is close to grand total (handles partial payments completing)
        if ($totalPaid > 0 && abs($grandTotal - $totalPaid) <= $tolerance) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnTotalPaid(): fallback condition 2 met (payment complete)');
            return true;
        }

        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnTotalPaid(): no conditions met');
        return false;
    }

    /**
     * New trigger logic based on order state
     */
    private function shouldTriggerWebhookOnOrderState(OrderInterface $order): bool
    {
        $configuredState = $this->config->getWebhookTriggerOrderState();
        $currentOrderState = $order->getState();
        
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnOrderState(): configured_state: ' . $configuredState);
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnOrderState(): current_order_state: ' . $currentOrderState);
        
        // If no specific state is configured, fall back to default logic for safety
        if (empty($configuredState)) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnOrderState(): no state configured, falling back to total paid logic');
            return $this->shouldTriggerWebhookOnTotalPaid($order);
        }
        
        // Check if order state matches configured state
        if ($currentOrderState === $configuredState) {
            $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnOrderState(): order state matches configured state');
            return true;
        }
        
        $this->debugger->debug('TriggerPurchaseWebhookEvent::shouldTriggerWebhookOnOrderState(): order state does not match configured state');
        return false;
    }
}
