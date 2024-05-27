<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Sales\Api\Data\OrderInterface;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Order\OrderItems;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class Purchase implements EventInterface
{
    private ?OrderInterface $order = null;
    private OrderItems $orderItems;
    private Config $config;
    private PriceFormatter $priceFormatter;

    public function __construct(
        OrderItems $orderItems,
        Config $config,
        PriceFormatter $priceFormatter
    ) {
        $this->orderItems = $orderItems;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
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
                'value' => $this->priceFormatter->format($this->getPurchaseValue($order)),
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

    private function getPurchaseValue(OrderInterface $order): float
    {
        return (float)$order->getGrandTotal() - (float)$order->getTaxAmount() - (float)$order->getShippingAmount();
    }
}
