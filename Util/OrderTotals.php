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
            return (float)$order->getBaseSubtotal() - abs((float)$order->getBaseDiscountAmount());
        }

        return (float)$order->getSubtotal() - abs((float)$order->getDiscountAmount());
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
        $orderValue = (float)$order->getSubtotal() - abs((float)$order->getDiscountAmount());

        if ($this->config->includeShippingInAdjustedTotal()) {
            $orderValue += (float)$order->getShippingAmount() - (float)$order->getShippingDiscountAmount();
        }

        $maxTransactionValue = $this->config->getMaxTransactionValue();

        if ($maxTransactionValue <= 0) {
            return $orderValue;
        }

        return min($orderValue, $maxTransactionValue);
    }
}
