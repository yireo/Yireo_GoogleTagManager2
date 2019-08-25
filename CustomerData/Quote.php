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

use Magento\Checkout\Model\Cart;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;

/**
 * Class \Yireo\GoogleTagManager2\CustomerData\Quote
 */
class Quote implements SectionSourceInterface
{
    /**
     * @var QuoteModel
     */
    private $quote;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @param Cart $cart
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Cart $cart,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->cart = $cart;
        $this->order = $cart->getLastRealOrder();
        $this->quote = $cart->getQuote();
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getSectionData()
    {
        if ($this->hasQuote() === false) {
            return [
            ];
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
        return (string) $this->quote->getQuoteCurrencyCode();
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
     * @return array
     * @throws LocalizedException
     */
    private function getItemsAsArray()
    {
        $quote = $this->quote;
        $data = [];

        foreach ($quote->getItemsCollection() as $item) {
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
