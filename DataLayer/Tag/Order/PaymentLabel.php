<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Order;

use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Order;

class PaymentLabel implements TagInterface
{
    private Order $order;

    public function __construct(
        Order $order
    ) {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        $payment = $this->order->getOrder()->getPayment();
        return $payment ? $payment->getMethod() : '';
    }
}
