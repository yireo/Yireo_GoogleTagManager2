<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Class \Yireo\GoogleTagManager2\CustomerData\Order
 */
class Order implements SectionSourceInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;


    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\Currency $currency
    ) {
        if (!$checkoutSession->getLastRealOrderId()) {
            return;
        }

        $this->order = $orderFactory->create()->loadByIncrementId($checkoutSession->getLastRealOrderId());
        $this->scopeConfig = $scopeConfig;
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        if ($this->hasOrder() === false) {
            return [];
        }

        return [
            'transactionEntity' => 'ORDER',
            'transactionId' => $this->getId(),
            'transactionDate' => $this->getDate(),
            'transactionAffiliation' => $this->getWebsiteName(),
            'transactionTotal' => $this->getGrandTotalAmount(),
            'transactionSubtotal' => $this->getSubTotalAmount(),
            'transactionTax' => $this->getTaxAmount(),
            'transactionShipping' => $this->getShippingAmount(),
            'transactionPayment' => $this->getPaymentLabel(),
            'transactionCurrency' => $this->getBaseCurrency(),
            'transactionPromoCode'=> $this->getPromoCode(),
            'transactionProducts' => $this->getItemsAsArray()
        ];
    }

    /**
     * @return int
     */
    private function getId()
    {
        return $this->order->getIncrementId();
    }

    /**
     * @return string
     */
    private function getDate()
    {
        return $this->order->getCreatedAt();
    }

    /**
     * @return string
     */
    private function getWebsiteName()
    {
        return $this->scopeConfig->getValue('general/store_information/name');
    }

    /**
     * @return string
     */
    private function getBaseCurrency()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * @return float
     */
    private function getGrandTotalAmount()
    {
        return (float)$this->order->getGrandTotal();
    }

    /**
     * @return float
     */
    private function getSubTotalAmount()
    {
        return (float)$this->order->getSubTotal();
    }

    /**
     * @return float
     */
    private function getShippingAmount()
    {
        return (float)$this->order->getShippingAmount();
    }

    /**
     * @return mixed
     */
    private function getPaymentLabel()
    {
        return $this->order->getPayment()->getMethodInstance()->getTitle();
    }

    /**
     * @return float
     */
    private function getTaxAmount()
    {
        return (float) $this->order->getGrandTotal() - $this->order->getSubtotal();
    }

    /**
     * @return string
     */
    private function getPromoCode()
    {
        return (string) $this->order->getCouponCode();
    }

    /**
     * @return bool
     */
    private function hasOrder()
    {
        $order = $this->order;

        if (empty($this->order)) {
            return false;
        }

        if ($order->getItems()) {
            return true;
        }

        return false;
    }

    /**
     * Return all order items as array
     *
     * @return string
     */
    private function getItemsAsArray()
    {
        $order = $this->order;
        $data = array();

        foreach($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            $data[] = array(
                'productId' => $item->getProduct()->getId(),
                'sku' => $item->getProduct()->getSku(),
                'name' => $item->getProduct()->getName(),
                'price' => $item->getProduct()->getPrice(),
                'quantity' => $item->getQty(),
            );
        }

        return $data;
    }
}