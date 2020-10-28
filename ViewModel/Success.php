<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\GoogleTagManager2\Block\Generic;
use Yireo\GoogleTagManager2\Config\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Success
 */
class Success implements ArgumentInterface
{
    /**
     * @var \Yireo\GoogleTagManager2\Helper\Data
     */
    private $config;
    
    /**
     * @var Generic
     */
    private $generic;
    
    /**
     * @var Session
     */
    private $checkoutSession;
    
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Generic constructor.
     * @param Config $config
     * @param Generic $generic
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $config,
        Generic $generic,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->generic = $generic;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * @param $attribute
     * @param $value
     * @return object
     */
    public function addAttribute($attribute, $value)
    {
        return $this->generic->addAttribute($attribute, $value);
    }

    /**
     * @return OrderItemInterface[]
     */
    public function getOrderData()
    {
        if ($this->hasOrder() === false) {
            return [];
        }

        $order = $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());

        return [
            'transactionEntity' => 'ORDER',
            'transactionId' => (string) $order->getIncrementId(),
            'transactionDate' => (string) $order->getCreatedAt(),
            'transactionAffiliation' => (string) $this->scopeConfig->getValue('general/store_information/name'),
            'transactionTotal' => (float) $order->getGrandTotal(),
            'transactionSubtotal' => (float) $order->getSubTotal(),
            'transactionTax' => (float) $order->getTaxAmount(),
            'transactionShipping' => (float) $order->getShippingAmount(),
            'transactionPayment' => $this->getPaymentLabel($order),
            'transactionCurrency' => (string) $order->getOrderCurrencyCode(),
            'transactionPromoCode' => (string) $order->getCouponCode(),
            'transactionProducts' => $this->getItemsAsArray($order)
        ];
    }

    /**
     * @return OrderInterface
     */
    private function getOrder()
    {
        return $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getPaymentLabel($order): string
    {
        $payment = $order->getPayment();

        return $payment ? $payment->getMethod() : '';
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getItemsAsArray($order): array
    {
        $data = [];

        foreach ($order->getItemsCollection() as $item) {
            /** @var Item $item */
            $data[] = [
                'productId' => $item->getProductId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPriceInclTax(),
                'quantity' => $item->getQtyOrdered(),
            ];
        }

        return $data;
    }

    /**
     * @return bool
     */
    private function hasOrder(): bool
    {
        try {
            $this->getOrder();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
