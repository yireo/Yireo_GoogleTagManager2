<?php

declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Event;

use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Tagging\GTM\DataLayer\Tag\Order\OrderItems;
use Magento\Sales\Api\Data\OrderInterface;
use Tagging\GTM\Util\PriceFormatter;
use Tagging\GTM\Config\Config;
use Psr\Log\LoggerInterface;
use Tagging\GTM\Logger\Debugger;

class PurchaseWebhookEvent
{
    private $json;
    private $clientFactory;
    private $config;
    private $orderItems;
    private $priceFormatter;
    private LoggerInterface $logger;
    private Debugger $debugger;

    public function __construct(
        Json            $json,
        ClientFactory   $clientFactory,
        OrderItems      $orderItems,
        Config          $config,
        PriceFormatter  $priceFormatter,
        LoggerInterface $logger,
        Debugger $debugger
    ) {
        $this->json = $json;
        $this->clientFactory = $clientFactory;
        $this->orderItems = $orderItems;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
        $this->logger = $logger;
        $this->debugger = $debugger;
    }

    public function purchase(OrderInterface $order)
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $marketingData = [];

        try {
            $this->debugger->debug("InvoicePaymentObserver: Processing order " . $order->getIncrementId());

            $extensionAttributes = $order->getExtensionAttributes();
            $this->debugger->debug("InvoicePaymentObserver: Extension attributes object: " . ($extensionAttributes ? 'exists' : 'is null'));

            if ($extensionAttributes) {
                $marketingData = $extensionAttributes->getTrytaggingMarketing();
                $this->debugger->debug("InvoicePaymentObserver: Marketing data: " . ($marketingData ?: 'is null'), $marketingData);
            }

            $rawMarketingData = $order->getData('trytagging_marketing');
            $this->debugger->debug("InvoicePaymentObserver: Raw marketing data from order: " . ($rawMarketingData ?: 'is null'), $rawMarketingData);

            if ($rawMarketingData) {
                $marketingData = $rawMarketingData;
            }

            if ($marketingData === null) {
                $marketingData = [
                    '_error' => 'trytagging_marketing data not found for order: ' . $order->getIncrementId()
                ];
            } else {
                $marketingData = $this->json->unserialize($marketingData);
            }
        } catch (\Exception $e) {
            $marketingData = [
                '_error' => $e->getMessage()
            ];
            $this->debugger->debug($e->getMessage());
        }

        $data = [
            'event' => 'trytagging_purchase',
            'marketing' => $marketingData,
            'store_domain' => $this->config->getStoreDomain(),
            'plugin_version' => $this->config->getVersion(),
            'ecommerce' => [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $this->config->getStoreName(),
                'currency' => $order->getOrderCurrencyCode(),
                'value' => $this->priceFormatter->format((float)$order->getGrandTotal()),
                'tax' => $this->priceFormatter->format((float)$order->getTaxAmount()),
                'shipping' => $this->priceFormatter->format((float)$order->getShippingInclTax()),
                'coupon' => $order->getCouponCode(),
                'items' => $this->orderItems->setOrder($order)->get()
            ]
        ];

        try {
            $data['user_data'] = [
                "customer_id" => $order->getCustomerId() ?? '',
                "billing_first_name" => $order->getBillingAddress() ? $order->getBillingAddress()->getFirstname() ?? '' : '',
                "billing_last_name" => $order->getBillingAddress() ? $order->getBillingAddress()->getLastname() ?? '' : '',
                "billing_address" => $order->getBillingAddress() && $order->getBillingAddress()->getStreet() ? $order->getBillingAddress()->getStreet()[0] ?? '' : '',
                "billing_postcode" => $order->getBillingAddress() ? $order->getBillingAddress()->getPostcode() ?? '' : '',
                "billing_country" => $order->getBillingAddress() ? $order->getBillingAddress()->getCountryId() ?? '' : '',
                "billing_state" => $order->getBillingAddress() ? $order->getBillingAddress()->getRegion() ?? '' : '',
                "billing_city" => $order->getBillingAddress() ? $order->getBillingAddress()->getCity() ?? '' : '',
                "billing_email" => $order->getBillingAddress() ? $order->getBillingAddress()->getEmail() ?? '' : '',
                "billing_phone" => $order->getBillingAddress() ? $order->getBillingAddress()->getTelephone() ?? '' : '',
                "shipping_first_name" => $order->getShippingAddress() ? $order->getShippingAddress()->getFirstname() ?? '' : '',
                "shipping_last_name" => $order->getShippingAddress() ? $order->getShippingAddress()->getLastname() ?? '' : '',
                "shipping_company" => $order->getShippingAddress() ? $order->getShippingAddress()->getCompany() ?? '' : '',
                "shipping_address" => $order->getShippingAddress() && $order->getShippingAddress()->getStreet() ? $order->getShippingAddress()->getStreet()[0] ?? '' : '',
                "shipping_postcode" => $order->getShippingAddress() ? $order->getShippingAddress()->getPostcode() ?? '' : '',
                "shipping_country" => $order->getShippingAddress() ? $order->getShippingAddress()->getCountryId() ?? '' : '',
                "shipping_state" => $order->getShippingAddress() ? $order->getShippingAddress()->getRegion() ?? '' : '',
                "shipping_city" => $order->getShippingAddress() ? $order->getShippingAddress()->getCity() ?? '' : '',
                "shipping_phone" => $order->getShippingAddress() ? $order->getShippingAddress()->getTelephone() ?? '' : '',
                "email" => $order->getCustomerEmail() ?? '',
                "first_name" => $order->getCustomerFirstname() ?? '',
                "last_name" => $order->getCustomerLastname() ?? '',
                "new_customer" => (string)($order->getCustomerIsGuest() ? "true" : "false")
            ];
        } catch (\Exception $e) {
            $this->debugger->debug($e->getMessage());
        }

        $client = $this->clientFactory->create();
        $client->addHeader('Content-Type', 'application/json');
        $client->addHeader('Accept', 'application/json');

        try {
            $url = $this->config->getGoogleTagmanagerUrl();
            $client->post('https://' . $url . '/order_created', $this->json->serialize($data));
            
            $statusCode = $client->getStatus();
            $this->debugger->debug('PurchaseWebhookEvent::purchase(): HTTP status code: ' . $statusCode);
            
            // Handle different HTTP status codes
            if ($statusCode === 200) {
                $this->debugger->debug('PurchaseWebhookEvent::purchase(): webhook sent successfully');
                return true;
            } elseif ($statusCode === 425) {
                // 425 Too Early - server is not ready to process the request
                $this->debugger->debug('PurchaseWebhookEvent::purchase(): received 425 Too Early, will retry later');
                $this->logger->info('Webhook received 425 Too Early for order: ' . $order->getIncrementId() . ', retry recommended');
                return false; // This will trigger retry logic in the observer
            } elseif ($statusCode >= 500) {
                // Server errors - temporary, should retry
                $this->debugger->debug('PurchaseWebhookEvent::purchase(): server error (5xx), will retry later');
                $this->logger->warning('Webhook server error ' . $statusCode . ' for order: ' . $order->getIncrementId());
                return false;
            } elseif ($statusCode >= 400) {
                // Client errors - likely permanent, log and don't retry
                $this->debugger->debug('PurchaseWebhookEvent::purchase(): client error (4xx), will not retry');
                $this->logger->error('Webhook client error ' . $statusCode . ' for order: ' . $order->getIncrementId());
                return true; // Don't retry client errors to avoid infinite loops
            } else {
                // Unexpected status code
                $this->debugger->debug('PurchaseWebhookEvent::purchase(): unexpected status code: ' . $statusCode);
                $this->logger->warning('Webhook unexpected status ' . $statusCode . ' for order: ' . $order->getIncrementId());
                return false;
            }
        } catch (\Exception $e) {
            $this->debugger->debug('PurchaseWebhookEvent::purchase(): exception: ' . $e->getMessage());
            $this->logger->error('Webhook exception for order ' . $order->getIncrementId() . ': ' . $e->getMessage());
            return false; // Network errors should be retried
        }
    }
}
