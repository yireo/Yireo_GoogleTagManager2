<?php

namespace AdPage\GTM\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class AddOrderMarketingField
{
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        // Ensure the custom field is always loaded
        $order->setData('trytagging_marketing', $order->getData('trytagging_marketing'));
        return $order;
    }
}
