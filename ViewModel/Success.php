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
            'transactionId' => (string) $order->getIncrementId(),
            'transactionDate' => (string) $order->getCreatedAt(),
            'transactionAffiliation' => $this->config->getStoreName(),
            'transactionTotal' => (float) $order->getGrandTotal(),
            'transactionSubtotal' => (float) $order->getSubTotal(),
            'transactionTax' => (float) $order->getTaxAmount(),
            'transactionShipping' => (float) $order->getShippingAmount(),
            'transactionPayment' => $this->getPaymentLabel($order),
            'transactionCurrency' => (string) $order->getOrderCurrencyCode(),
            'transactionPromoCode' => (string) $order->getCouponCode(),
            'transactionProducts' => $this->getItemsAsArray($order)
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
            $data[] = [
                'productId' => $item->getProductId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPriceInclTax(),
                'quantity' => $item->getQtyOrdered(),
            ];
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
}
