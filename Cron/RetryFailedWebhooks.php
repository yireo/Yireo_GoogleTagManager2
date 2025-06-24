<?php

declare(strict_types=1);

namespace Tagging\GTM\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Tagging\GTM\DataLayer\Event\PurchaseWebhookEvent;
use Tagging\GTM\Logger\Debugger;
use Psr\Log\LoggerInterface;

class RetryFailedWebhooks
{
    private OrderRepositoryInterface $orderRepository;
    private OrderPaymentRepositoryInterface $orderPaymentRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterBuilder $filterBuilder;
    private PurchaseWebhookEvent $webhookEvent;
    private Debugger $debugger;
    private LoggerInterface $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        PurchaseWebhookEvent $webhookEvent,
        Debugger $debugger,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->webhookEvent = $webhookEvent;
        $this->debugger = $debugger;
        $this->logger = $logger;
    }

    /**
     * Retry failed webhooks for orders that haven't been processed yet
     * This runs as a fallback mechanism to catch orders that were missed
     */
    public function execute()
    {
        $this->debugger->debug('RetryFailedWebhooks::execute(): Starting retry process');
        
        $maxRetries = 3;
        $retryAfterHours = 2; // Retry after 2 hours
        $maxAgeHours = 168; // Don't retry orders older than 1 week
        
        $ordersToRetry = $this->getOrdersToRetry($maxRetries, $retryAfterHours, $maxAgeHours);
        
        if (empty($ordersToRetry)) {
            $this->debugger->debug('RetryFailedWebhooks::execute(): No orders to retry');
            return;
        }
        
        $this->debugger->debug('RetryFailedWebhooks::execute(): Found ' . count($ordersToRetry) . ' orders to retry');
        
        foreach ($ordersToRetry as $order) {
            $this->retryOrderWebhook($order);
        }
        
        $this->debugger->debug('RetryFailedWebhooks::execute(): Retry process completed');
    }

    /**
     * Get orders that need webhook retry
     */
    private function getOrdersToRetry(int $maxRetries, int $retryAfterHours, int $maxAgeHours): array
    {
        $fromDate = date('Y-m-d H:i:s', strtotime('-' . $maxAgeHours . ' hours'));
        $retryAfterTime = time() - ($retryAfterHours * 3600);
        
        // Get orders created in the last week that are in processing/complete state
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $fromDate, 'gteq')
            ->addFilter('state', ['processing', 'complete'], 'in')
            ->create();
            
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        $ordersToRetry = [];
        
        foreach ($orders as $order) {
            $payment = $order->getPayment();
            if (!$payment) {
                continue;
            }
            
            $webhookProcessed = $payment->getAdditionalInformation('trytagging_webhook_processed');
            $failedAttempts = (int)$payment->getAdditionalInformation('trytagging_webhook_failed_attempts');
            $lastAttempt = (int)$payment->getAdditionalInformation('trytagging_webhook_last_attempt');
            
            // Skip if already processed successfully
            if ($webhookProcessed) {
                continue;
            }
            
            // Skip if max retries reached
            if ($failedAttempts >= $maxRetries) {
                continue;
            }
            
            // Skip if not enough time has passed since last attempt
            if ($lastAttempt && $lastAttempt > $retryAfterTime) {
                continue;
            }
            
            // Check if order is fully paid
            $grandTotal = (float)$order->getGrandTotal();
            $totalPaid = (float)$order->getTotalPaid();
            $tolerance = 0.01;
            
            if (abs($grandTotal - $totalPaid) <= $tolerance) {
                $ordersToRetry[] = $order;
            }
        }
        
        return $ordersToRetry;
    }

    /**
     * Retry webhook for a specific order
     */
    private function retryOrderWebhook(OrderInterface $order)
    {
        $this->debugger->debug('RetryFailedWebhooks::retryOrderWebhook(): Retrying webhook for order ' . $order->getIncrementId());
        
        try {
            $payment = $order->getPayment();
            $failedAttemptsKey = 'trytagging_webhook_failed_attempts';
            $failedAttempts = (int)$payment->getAdditionalInformation($failedAttemptsKey);
            
            $success = $this->webhookEvent->purchase($order);
            
            if ($success) {
                $payment->setAdditionalInformation('trytagging_webhook_processed', true);
                $payment->setAdditionalInformation($failedAttemptsKey, null);
                $payment->setAdditionalInformation('trytagging_webhook_last_attempt', null);
                $this->debugger->debug('RetryFailedWebhooks::retryOrderWebhook(): Webhook retry successful for order ' . $order->getIncrementId());
                $this->logger->info('Webhook retry successful for order: ' . $order->getIncrementId());
            } else {
                $newFailedAttempts = $failedAttempts + 1;
                $payment->setAdditionalInformation($failedAttemptsKey, $newFailedAttempts);
                $payment->setAdditionalInformation('trytagging_webhook_last_attempt', time());
                $this->debugger->debug('RetryFailedWebhooks::retryOrderWebhook(): Webhook retry failed for order ' . $order->getIncrementId() . ', attempt #' . $newFailedAttempts);
                $this->logger->warning('Webhook retry failed for order: ' . $order->getIncrementId() . ', attempt #' . $newFailedAttempts);
            }
            
            $this->orderPaymentRepository->save($payment);
            
        } catch (\Exception $e) {
            $this->debugger->debug('RetryFailedWebhooks::retryOrderWebhook(): Exception during retry for order ' . $order->getIncrementId() . ': ' . $e->getMessage());
            $this->logger->error('Exception during webhook retry for order ' . $order->getIncrementId() . ': ' . $e->getMessage());
        }
    }
} 