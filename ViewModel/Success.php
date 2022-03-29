<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Success
 */
class Success implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Session
     */
    private $checkoutSession;
    
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Generic constructor.
     * @param Config $config
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Config $config,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return array
     */
    public function getOrderAttributes(): array
    {
        if ($this->hasOrder() === false) {
            return [];
        }

        $order = $this->getOrder();
        return [
            'transactionEntity' => 'ORDER',
            'transactionId' => $this->getTransactionId($order),
            'transactionDate' => (string) $order->getCreatedAt(),
            'transactionAffiliation' => $this->getTransactionAffiliation(),
            'transactionTotal' => $this->getTransactionTotal($order),
            'transactionSubtotal' => (float) $order->getSubTotal(),
            'transactionTax' => $this->getTransactionTax($order),
            'transactionShipping' => $this->getTransactionShipping($order),
            'transactionPayment' => $this->getPaymentLabel($order),
            'transactionCurrency' => (string) $order->getOrderCurrencyCode(),
            'transactionPromoCode' => $this->getTransactionPromoCode($order),
            'transactionProducts' => $this->getItemsAsArray($order),
            'ecommerce' => $this->getEcommerceAttributesAsArray($order),
            'event' => 'transaction',
        ];
    }

    /**
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->checkoutSession->getLastRealOrder()->getId());
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getPaymentLabel(OrderInterface $order): string
    {
        $payment = $order->getPayment();
        return $payment ? $payment->getMethod() : '';
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

    private function getEcommerceAttributesAsArray(OrderInterface $order): array
    {
        return [
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
        ];
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
