<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Order\OrderItems;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

// See https://developers.google.com/analytics/devguides/collection/ga4/reference/events?client_type=gtm#purchase
class Purchase implements EventInterface
{
    private ?OrderInterface $order = null;
    private OrderItems $orderItems;
    private Config $config;
    private PriceFormatter $priceFormatter;
    private Session $checkoutSession;

    public function __construct(
        OrderItems $orderItems,
        Config $config,
        PriceFormatter $priceFormatter,
        Session $checkoutSession
    ) {
        $this->orderItems = $orderItems;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $order = $this->getOrder();
        if (false === $order instanceof OrderInterface) {
            return [];
        }

        if (false === in_array($order->getState(), $this->getOrderStates())) {
            return [];
        }

        return [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $this->config->getStoreName(),
                'currency' => $order->getOrderCurrencyCode(),
                'value' => $this->priceFormatter->format((float)$order->getSubtotal()),
                'tax' => $this->priceFormatter->format((float)$order->getTaxAmount()),
                'shipping' => $this->priceFormatter->format((float)$order->getShippingAmount()),
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

    private function getOrder(): ?OrderInterface
    {
        if ($this->order instanceof OrderInterface) {
            return $this->order;
        }

        $this->order = $this->checkoutSession->getLastRealOrder();
        return $this->order;
    }

    private function getOrderStates(): array
    {
        $orderStates = $this->config->getOrderStatesForPurchaseEvent();
        if (!empty($orderStates)){
            return $orderStates;
        }

        return $this->getDefaultOrderStates();
    }

    private function getDefaultOrderStates(): array
    {
        return [
            Order::STATE_PENDING_PAYMENT,
            Order::STATE_PAYMENT_REVIEW,
            Order::STATE_HOLDED,
            Order::STATE_PROCESSING,
            Order::STATE_COMPLETE,
        ];
    }
}
