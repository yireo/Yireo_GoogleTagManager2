<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api\Data;

use Magento\Sales\Api\Data\OrderItemInterface;

interface OrderItemTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function setOrderItem(OrderItemInterface $orderItem);
}
