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
 * Class \Yireo\GoogleTagManager2\CustomerData\Quote
 */
class Quote implements SectionSourceInterface
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\Currency $currency
    ) {
        $this->cart = $cart;
        $this->order = $cart->getLastRealOrder();
        $this->quote = $cart->getQuote();
        $this->scopeConfig = $scopeConfig;
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        if ($this->hasQuote() === false) {
            return [];
        }

        return [
            'transactionEntity' => 'QUOTE',
            'transactionId' => $this->getId(),
            'transactionAffiliation' => $this->getWebsiteName(),
            'transactionTotal' => $this->getTotalAmount(),
            'transactionTax' => $this->getTaxAmount(),
            'transactionCurrency' => $this->getBaseCurrency(),
            'transactionProducts' => $this->getItemsAsArray()
        ];
    }

    /**
     * @return int
     */
    private function getId()
    {
        return $this->quote->getId();
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
    private function getTotalAmount()
    {
        return (float)$this->quote->getGrandTotal();
    }

    /**
     * @return float
     */
    private function getTaxAmount()
    {
        return (float)$this->quote->getGrandTotal() - $this->quote->getSubtotal();
    }

    /**
     * @return bool
     */
    private function hasQuote()
    {
        $quote = $this->quote;
        if ($quote->getItems()) {
            return true;
        }

        return false;
    }

    /**
     * Return all quote items as array
     *
     * @return string
     */
    private function getItemsAsArray()
    {
        $quote = $this->quote;
        $data = [];

        foreach ($quote->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
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
