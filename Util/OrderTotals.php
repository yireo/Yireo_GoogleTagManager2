<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Sales\Api\Data\OrderInterface;

class OrderTotals
{
    public function getShippingTotal(OrderInterface $order): float
    {
        return (float)$order->getShippingAmount() - (float)$order->getShippingDiscountAmount();
    }
}
