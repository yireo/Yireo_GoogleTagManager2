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

use Magento\Checkout\Model\Session\Proxy;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * Order constructor.
     *
     * @param Proxy $checkoutSession
     * @param OrderFactory $orderFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Currency $currency
     */
    public function __construct(
        Proxy $checkoutSession,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig,
        Currency $currency
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
     * @throws LocalizedException
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
            'transactionPromoCode' => $this->getPromoCode(),
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
     * @throws LocalizedException
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
        return (float)$this->order->getTaxAmount();
    }

    /**
     * @return string
     */
    private function getPromoCode()
    {
        return (string)$this->order->getCouponCode();
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
     * @return array
     * @throws LocalizedException
     */
    private function getItemsAsArray()
    {
        $order = $this->order;
        $data = [];

        foreach ($order->getItemsCollection() as $item) {
            /** @var Item $item */
            $data[] = [
                'productId' => $item->getProduct()->getId(),
                'sku' => $item->getProduct()->getSku(),
                'name' => $item->getProduct()->getName(),
                'price' => $item->getProduct()->getPrice(),
                'quantity' => $item->getQty(),
            ];
        }

        return $data;
    }
}
