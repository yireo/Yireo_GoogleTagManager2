<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Block;

/**
 * Class \Yireo\GoogleTagManager2\Block\Quote
 */
class Quote extends Generic
{
    /**
     * Return all quote items as array
     *
     * @return string
     */
    public function getItemsAsArray()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quote;
        if (empty($quote)) {
            return array();
        }

        $data = array();
        foreach($quote->getAllItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            $data[] = array(
                'sku' => $item->getProduct()->getSku(),
                'name' => $item->getProduct()->getName(),
                'price' => $item->getProduct()->getPrice(),
                'quantity' => $item->getQty(),
            );
        }

        return $data;
    }

    /**
     * Return all quote items as JSON
     *
     * @return string
     */
    public function getItemsAsJson()
    {   
        return json_encode($this->getItemsAsArray());
    }
}
