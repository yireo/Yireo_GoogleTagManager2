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

use \Magento\Framework\View\Element\Template;

/**
 * Class \Yireo\GoogleTagManager2\Block\Generic
 */
class Generic extends Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Yireo\GoogleTagManager2\Helper\Data $helper
     * @param \Yireo\GoogleTagManager2\Model\Container $container
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Yireo\GoogleTagManager2\Helper\Data $helper,
        \Yireo\GoogleTagManager2\Model\Container $container,
        array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->container = $container;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Return whether this module is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * Check whether this module is in debugging mode
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->helper->isDebug();
    }

    /**
     * Get the GA ID
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->helper->getId();
    }

    /**
     * Return a configuration value
     *
     * @param null $key
     * @param null $default_value
     *
     * @return mixed
     */
    public function getConfig($key = null, $default_value = null)
    {
        return $this->helper->getConfigValue($key, $default_value);
    }

    /**
     * Determine whether this GA configuration has any attributes
     *
     * @return bool
     */
    public function hasAttributes()
    {
        $attributes = $this->getAttributes();
        if (!empty($attributes)) {
            return true;
        }

        return false;
    }

    /**
     * Return all attributes as JSON
     *
     * @return string
     */
    public function getAttributesAsJson()
    {
        $attributes = $this->getAttributes();
        return json_encode($attributes);
    }

    /**
     * Add a new attribute to the GA container
     *
     * @param $name
     * @param $value
     *
     * @return object
     */
    public function addAttribute($name, $value)
    {
        return $this->container->setData($name, $value);
    }

    /**
     * Get the configured attributes for a GA container
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->container->getData();
    }

    /**
     * Return the current store information
     *
     * @return mixed
     */
    public function getWebsiteName()
    {
        //return $this->storeManager->getStore()->getName();
        return $this->scopeConfig->getValue('general/store_information/name');
    }
}
