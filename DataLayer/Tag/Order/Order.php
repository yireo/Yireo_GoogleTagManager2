<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\GoogleTagManager2\Api\Data\MergeTagInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\OrderTotals;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class Order implements MergeTagInterface
{
    private CheckoutSession $checkoutSession;
    private OrderRepositoryInterface $orderRepository;
    private Config $config;
    private PriceFormatter $priceFormatter;
    private OrderTotals $orderTotals;

    /**
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        Config $config,
        PriceFormatter $priceFormatter,
        OrderTotals $orderTotals
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
        $this->orderTotals = $orderTotals;
    }

    /**
     * @return array
     */
    public function merge(): array
    {
        $order = $this->getOrder();
        return [
            'currency' => (string)$order->getOrderCurrencyCode(),
            'value' => $this->priceFormatter->format($this->orderTotals->getValueTotal($order)),
            'tax' => $this->priceFormatter->format((float)$order->getTaxAmount()),
            'shipping' => $this->priceFormatter->format($this->orderTotals->getShippingTotal($order)),
            'affiliation' => $this->config->getStoreName(),
            'transaction_id' => $order->getIncrementId(),
            'coupon' => $order->getCouponCode(),
            'payment_method' => $order->getPayment() ? $order->getPayment()->getMethod() : ''
        ];
    }

    /**
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
    }
}
