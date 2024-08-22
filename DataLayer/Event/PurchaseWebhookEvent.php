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

class PurchaseWebhookEvent
{
    private $json;
    private $clientFactory;
    private $config;
    private $orderItems;
    private $priceFormatter;
    private LoggerInterface $logger;

    public function __construct(
        Json            $json,
        ClientFactory   $clientFactory,
        OrderItems      $orderItems,
        Config          $config,
        PriceFormatter  $priceFormatter,
        LoggerInterface $logger
    ) {
        $this->json = $json;
        $this->clientFactory = $clientFactory;
        $this->orderItems = $orderItems;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
        $this->logger = $logger;
    }

    public function purchase(OrderInterface $order)
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $marketingData = [];

        try {
            $connection = $order->getResource()->getConnection();
            $tableName = $order->getResource()->getMainTable();
            $orderId = $order->getId();
            
            $select = $connection->select()
                ->from($tableName, ['trytagging_marketing'])
                ->where('entity_id = ?', $orderId);
            
            $marketingData = $connection->fetchOne($select);
            
            if ($marketingData === false) {
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
            $this->logger->error($e->getMessage());
        }

        $data = [
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

        $data['event'] = 'trytagging_purchase';
        $client = $this->clientFactory->create();
        $client->addHeader('Content-Type', 'application/json');
        $client->addHeader('Accept', 'application/json');

        try {
            $url = $this->config->getGoogleTagmanagerUrl();
            $client->post('https://' . $url . '/order_created', $this->json->serialize($data));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $client->getStatus() == 200;
    }
}
