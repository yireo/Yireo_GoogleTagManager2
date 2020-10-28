<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Directory\Model\Currency;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\Config\Config;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Generic
 */
class Generic implements ArgumentInterface
{
    /**
     * @var Currency
     */
    private $currency;
    /**
     * @var Config
     */
    private $config;

    /**
     * Generic constructor.
     *
     * @param Currency $currency
     * @param Config $config
     */
    public function __construct(
        Currency $currency,
        Config $config
    ) {
        $this->currency = $currency;
        $this->config = $config;
    }

    /**
     *
     */
    public function getBaseCurrencyCode()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * Return whether this module is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }
}
