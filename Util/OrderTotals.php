<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Config\Config;

class OrderTotals
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getValueTotal(OrderInterface $order): float
    {
        if ($this->config->useBaseCurrency()) {
            return $this->getBaseItemsValue($order);
        }

        return $this->getItemsValue($order);
    }

    public function getShippingTotal(OrderInterface $order): float
    {
        if ($this->config->useBaseCurrency()) {
            return (float)$order->getBaseShippingAmount() - (float)$order->getBaseShippingDiscountAmount();
        }

        return (float)$order->getShippingAmount() - (float)$order->getShippingDiscountAmount();
    }

    /**
     * Calculate the adjusted transaction value based on the configured maximum
     * Note: This always uses store currency, not base currency
     *
     * @param OrderInterface $order
     * @return float
     */
    public function getValueTotalAjusted(OrderInterface $order): float
    {
        $orderValue = $this->getItemsValue($order);

        if ($this->config->includeShippingInAdjustedValue()) {
            $orderValue += (float)$order->getShippingAmount() - (float)$order->getShippingDiscountAmount();
        }

        $maxTransactionValue = $this->config->getMaxTransactionValue();

        if ($maxTransactionValue <= 0) {
            return $orderValue;
        }

        return min($orderValue, $maxTransactionValue);
    }

    /**
     * Subtotal minus the discount on items, in order currency.
     *
     * The discount_amount of an order also includes the discount on shipping
     * (shipping_discount_amount), which is already subtracted from the shipping
     * total. So only the remaining part of the discount is subtracted here.
     */
    private function getItemsValue(OrderInterface $order): float
    {
        $itemsDiscount = abs((float)$order->getDiscountAmount()) - abs((float)$order->getShippingDiscountAmount());

        return (float)$order->getSubtotal() - $itemsDiscount;
    }

    /**
     * Subtotal minus the discount on items, in base currency.
     */
    private function getBaseItemsValue(OrderInterface $order): float
    {
        $itemsDiscount = abs((float)$order->getBaseDiscountAmount())
            - abs((float)$order->getBaseShippingDiscountAmount());

        return (float)$order->getBaseSubtotal() - $itemsDiscount;
    }
}
