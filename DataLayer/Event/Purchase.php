<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Order\OrderItems;

class Purchase implements EventInterface
{
    private ?OrderInterface $order = null;
    private OrderItems $orderItems;
    private Config $config;

    public function __construct(
        OrderItems $orderItems,
        Config $config
    ) {
        $this->orderItems = $orderItems;
        $this->config = $config;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $order = $this->order;
        return [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $this->config->getStoreName(),
                'currency' => $order->getOrderCurrencyCode(),
                'value' => (float)$order->getGrandTotal(),
                'tax' => (float)$order->getTaxAmount(),
                'shipping' => (float)$order->getShippingAmount(),
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
