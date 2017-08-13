<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Generic
 */
class Generic
{
    private $currency;

    /**
     * Generic constructor.
     *
     * @param \Magento\Directory\Model\Currency $currency
     */
    public function __construct(
        \Magento\Directory\Model\Currency $currency
    )
    {
        $this->currency = $currency;
    }

    /**
     *
     */
    public function getBaseCurrencyCode()
    {
        return $this->currency->getCurrencySymbol();
    }
}
