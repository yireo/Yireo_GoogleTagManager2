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
}
