<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api\Data;

use Magento\Sales\Api\Data\OrderInterface;

interface OrderTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function setOrder(OrderInterface $order);
}
