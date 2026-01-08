<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Order\OrderItems;
use Yireo\GoogleTagManager2\Util\OrderTotals;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

// See https://developers.google.com/analytics/devguides/collection/ga4/reference/events?client_type=gtm#purchase
class Purchase implements EventInterface
{
    private ?OrderInterface $order = null;
    private OrderItems $orderItems;
    private Config $config;
    private PriceFormatter $priceFormatter;
    private Session $checkoutSession;
    private OrderTotals $orderTotals;

    public function __construct(
        OrderItems $orderItems,
        Config $config,
        PriceFormatter $priceFormatter,
        Session $checkoutSession,
        OrderTotals $orderTotals
    ) {
        $this->orderItems = $orderItems;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
        $this->checkoutSession = $checkoutSession;
        $this->orderTotals = $orderTotals;
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

        if ($this->config->hasServerSideTracking() && false === in_array($order->getState(), $this->getOrderStates())) {
            return [];
        }

        $currencyCode = $this->config->useBaseCurrency()
            ? $order->getBaseCurrencyCode()
            : $order->getOrderCurrencyCode();

        $taxAmount = $this->config->useBaseCurrency()
            ? (float)$order->getBaseTaxAmount()
            : (float)$order->getTaxAmount();

        return [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $this->config->getStoreName(),
                'currency' => $currencyCode,
                'value' => $this->priceFormatter->format($this->orderTotals->getValueTotal($order)),
                'tax' => $this->priceFormatter->format($taxAmount),
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
            Order::STATE_NEW,
            Order::STATE_PENDING_PAYMENT,
            Order::STATE_PAYMENT_REVIEW,
            Order::STATE_HOLDED,
            Order::STATE_PROCESSING,
            Order::STATE_COMPLETE,
        ];
    }
}
