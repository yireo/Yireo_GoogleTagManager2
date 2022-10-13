<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\GoogleTagManager2\Api\Data\MergeTagInterface;
use Yireo\GoogleTagManager2\Config\Config;

class Order implements MergeTagInterface
{
    private CheckoutSession $checkoutSession;
    private OrderRepositoryInterface $orderRepository;
    private Config $config;

    /**
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        Config $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function merge(): array
    {
        $order = $this->getOrder();
        return [
            'currency' => (string)$order->getOrderCurrencyCode(),
            'value' => $order->getGrandTotal(),
            'tax' => $order->getTaxAmount(),
            'shipping' => $order->getShippingAmount(),
            'affiliation' => $this->config->getStoreName(),
            'transaction_id' => $order->getIncrementId(),
            'coupon' => $order->getCouponCode()
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
