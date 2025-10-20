<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Order\OrderItems;
use Yireo\GoogleTagManager2\Util\OrderTotals;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

// See https://developers.google.com/analytics/devguides/collection/ga4/reference/events?client_type=gtm#refund
class Refund implements EventInterface
{
    private ?OrderInterface $order = null;
    private OrderItems $orderItems;
    private Config $config;
    private PriceFormatter $priceFormatter;
    private OrderTotals $orderTotals;

    public function __construct(
        OrderItems $orderItems,
        Config $config,
        PriceFormatter $priceFormatter,
        OrderTotals $orderTotals
    ) {
        $this->orderItems = $orderItems;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
        $this->orderTotals = $orderTotals;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $order = $this->order;
        return [
            'event' => 'refund',
            'ecommerce' => [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $this->config->getStoreName(),
                'currency' => $order->getOrderCurrencyCode(),
                'value' => $this->priceFormatter->format($this->orderTotals->getValueTotal($order)),
                'tax' => $this->priceFormatter->format((float)$order->getTaxAmount()),
                'shipping' => $this->priceFormatter->format($this->orderTotals->getShippingTotal($order)),
                'coupon' => $order->getCouponCode(),
                'payment_method' => $order->getPayment() ? $order->getPayment()->getMethod() : '',
                'payment_type' => $order->getPayment() ? $order->getPayment()->getMethod() : '',
                'items' => $this->orderItems->setOrder($order)->get(),
            ]
        ];
    }

    /**
     * @param OrderInterface $order
     * @return Refund
     */
    public function setOrder(OrderInterface $order): Refund
    {
        $this->order = $order;
        return $this;
    }
}
