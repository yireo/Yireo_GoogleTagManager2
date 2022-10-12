<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\Config;

class Order implements TagInterface
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
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
    }

    /**
     * @return bool
     */
    private function hasOrder(): bool
    {
        try {
            $this->getOrder();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getPaymentLabel(): string
    {
        $payment = $this->getOrder()->getPayment();
        return $payment ? $payment->getMethod() : '';
    }

    /**
     * @return array
     */
    public function get(): array
    {
        if ($this->hasOrder() === false) {
            return [];
        }

        $order = $this->getOrder();

        return [
            'transactionEntity' => 'ORDER',
            'transactionId' => $this->getTransactionId($order),
            'transactionDate' => (string)$order->getCreatedAt(),
            'transactionAffiliation' => $this->getTransactionAffiliation(),
            'transactionTotal' => $this->getTransactionTotal($order),
            'transactionSubtotal' => (float)$order->getSubTotal(),
            'transactionTax' => $this->getTransactionTax($order),
            'transactionShipping' => $this->getTransactionShipping($order),
            'transactionPayment' => $this->getPaymentLabel($order),
            'transactionCurrency' => (string)$order->getOrderCurrencyCode(),
            'transactionPromoCode' => $this->getTransactionPromoCode($order),
            'transactionProducts' => $this->getItemsAsArray($order),
            'ecommerce' => [
                'currencyCode' => (string)$order->getOrderCurrencyCode(),
                'purchase' => [
                    'actionField' => [
                        'id' => $this->getTransactionId($order),
                        'affiliation' => $this->getTransactionAffiliation(),
                        'revenue' => $this->getTransactionTotal($order),
                        'tax' => $this->getTransactionTax($order),
                        'shipping' => $this->getTransactionShipping($order),
                        'coupon' => $this->getTransactionPromoCode($order),
                    ],
                    'products' => $this->getItemsAsArray($order),
                ],
            ],
            'event' => 'transaction',
        ];
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getItemsAsArray(OrderInterface $order): array
    {
        $data = [];

        foreach ($order->getItemsCollection([], true) as $item) {
            /** @var Item $item */
            $itemData = [
                'productId' => $item->getProductId(),
                'id' => $item->getProductId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPriceInclTax(),
                'quantity' => $item->getQtyOrdered(),
            ];
            $parentSku = $item->getProduct()->getData(ProductInterface::SKU);
            if ($parentSku !== $item->getSku()) {
                $itemData['parentsku'] = $parentSku;
            }
            $data[] = $itemData;
        }

        return $data;
    }

    private function getTransactionId(OrderInterface $order): string
    {
        return (string)$order->getIncrementId();
    }

    private function getTransactionAffiliation(): string
    {
        return $this->config->getStoreName();
    }

    private function getTransactionTotal(OrderInterface $order): float
    {
        return (float)$order->getGrandTotal();
    }

    private function getTransactionTax(OrderInterface $order): float
    {
        return (float)$order->getTaxAmount();
    }

    private function getTransactionShipping(OrderInterface $order): float
    {
        return (float)$order->getShippingAmount();
    }

    private function getTransactionPromoCode(OrderInterface $order): string
    {
        return (string)$order->getCouponCode();
    }
}
