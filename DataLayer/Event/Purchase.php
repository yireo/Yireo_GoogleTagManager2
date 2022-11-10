<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Order\OrderItems;

class Purchase implements EventInterface
{
    private ?OrderInterface $order;
    private OrderItems $orderItems;

    public function __construct(
        OrderItems $orderItems
    ) {
        $this->orderItems = $orderItems;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $order = $this->order;
        $affiliation = ''; // @todo
        return [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $affiliation,
                'currency' => $order->getOrderCurrencyCode(),
                'value' => $order->getGrandTotal(),
                'tax' => $order->getTaxAmount(),
                'shipping' => $order->getShippingAmount(),
                'coupon' => $order->getCouponCode(),
                'items' => $this->orderItems->setOrder($order)->get()
            ]
        ];
    }

    /**
     * @param OrderInterface $order
     * @return Purchase
     */
    public function setOrder(OrderInterface $order): Purchase
    {
        $this->order = $order;
        return $this;
    }
}
