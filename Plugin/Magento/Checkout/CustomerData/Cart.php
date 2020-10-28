<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2019 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Plugin\Magento\Checkout\CustomerData;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\CustomerData\Cart as CustomerData;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;

class Cart
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteModel
     */
    private $quote;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CheckoutCart $checkoutCart
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CheckoutCart $checkoutCart,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quote = $checkoutCart->getQuote();
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param CustomerData $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(CustomerData $subject, $result)
    {
        if (empty($result) || !is_array($result)) {
            return $result;
        }

        $result['gtm'] = [
            'transactionEntity' => 'QUOTE',
            'transactionId' => $this->getQuoteId(),
            'transactionAffiliation' => $this->getWebsiteName(),
            'transactionTotal' => $this->getTotalAmount(),
            'transactionTax' => $this->getTaxAmount(),
            'transactionCurrency' => $this->getBaseCurrency(),
            'transactionProducts' => $this->getItemsAsArray()
        ];

        return $result;
    }

    /**
     * @return int
     */
    private function getQuoteId()
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
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * @return string
     */
    private function getOrderId(): string
    {
        if ($this->hasOrder() === false) {
            return '';
        }

        return (string) $this->getOrder()->getIncrementId();
    }

    /**
     * @return bool
     */
    private function hasOrder()
    {
        $order = $this->getOrder();
        if ($order->getItems()) {
            return true;
        }

        return false;
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
