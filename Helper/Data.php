<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author    Jisse Reitsma <jisse@yireo.com>
 * @copyright 2017 Yireo (https://www.yireo.com/)
 * @license   Open Source License (OSL v3)
 */
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context as HelperContext;
use Yireo\GoogleTagManager2\Config\Config;

class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Data constructor.
     *
     * @param HelperContext $context
     * @param Config        $config
     */
    public function __construct(
        HelperContext $context,
        Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * Check whether the module is enabled
     *
     * @return     bool
     * @deprecated Use Config::isEnabled()
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * Check whether the module is in debugging mode
     *
     * @return     bool
     * @deprecated Use Config::isDebug()
     */
    public function isDebug()
    {
        return $this->config->isDebug();
    }

    /**
     * Return the GA ID
     *
     * @return     string
     * @deprecated Use Config::getId()
     */
    public function getId()
    {
        return $this->config->getId();
    }

    /**
     * Check whether the insertion method is the observer method
     *
     * @return     bool
     * @deprecated Use Config::isMethodObserver()
     */
    public function isMethodObserver()
    {
        return $this->config->isMethodObserver();
    }

    /**
     * Check whether the insertion method is the layout method
     *
     * @return     bool
     * @deprecated Use Config::isMethodLayout()
     */
    public function isMethodLayout()
    {
        return $this->config->isMethodLayout();
    }

    /**
     * Return a configuration value
     *
     * @param string $key          Configuration path
     * @param null   $defaultValue Default value for the key
     *
     * @return     mixed|null
     * @deprecated Use Config::getConfigValue()
     */
    public function getConfigValue(string $key = '', $defaultValue = null)
    {
        return $this->config->getConfigValue($key, $defaultValue);
    }

    /**
     * Debugging method
     *
     * @param string $string   String to debug
     * @param null   $variable Variable to dump to debug message
     *
     * @return bool
     */
    public function debug($string, $variable = null)
    {
        if ($this->config->isDebug() == false) {
            return false;
        }

        if (!empty($variable)) {
            $string .= ': ' . var_export($variable, true);
        }

        $this->_logger->info('Yireo_GoogleTagManager: ' . $string);

        return true;
    }
}
