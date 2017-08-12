<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Helper;

/**
 * Class \Yireo\GoogleTagManager2\Helper\Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Constant for the observer method
     */
    const METHOD_OBSERVER = 0;

    /**
     * Constant for the layout method
     */
    const METHOD_LAYOUT = 1;

    /**
     * <<<<<<< HEAD
     * =======
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    )
    {
        $this->layoutFactory = $layoutFactory;
        $this->blockFactory = $blockFactory;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrder = $salesOrder;
        $this->coreRegistry = $coreRegistry;
        $this->pricingHelper = $pricingHelper;

        parent::__construct($context);
    }

    /**
     * >>>>>>> 25d2d833233327ee95db80dcce2bb3edb2a20bc3
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getConfigValue('enabled', false);
    }

    /**
     * Check whether the module is in debugging mode
     *
     * @return bool
     */
    public function isDebug()
    {
        return (bool)$this->getConfigValue('debug');
    }

    /**
     * Return the GA ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->getConfigValue('id');
    }

    /**
     * Check whether the insertion method is the observer method
     *
     * @return bool
     */
    public function isMethodObserver()
    {
        return ($this->getConfigValue('method') == self::METHOD_OBSERVER);
    }

    /**
     * Check whether the insertion method is the layout method
     *
     * @return bool
     */
    public function isMethodLayout()
    {
        return ($this->getConfigValue('method') == self::METHOD_LAYOUT);
    }

    /**
     * Return a configuration value
     *
     * @param null $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function getConfigValue($key = null, $defaultValue = null)
    {
        $value = $this->scopeConfig->getValue(
            'googletagmanager2/settings/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * Debugging method
     *
     * @param $string
     * @param null $variable
     *
     * @return bool
     */
    public function debug($string, $variable = null)
    {
        if ($this->isDebug() == false) {
            return false;
        }

        if (!empty($variable)) {
            $string .= ': ' . var_export($variable, true);
        }

        $this->_logger->info('Yireo_GoogleTagManager: ' . $string);

        return true;
    }

    /**
     * @deprecated Use \Yireo\GoogleTagManager2\ViewModel\Generic::getBaseCurrencyCode() instead
     */
    public function getBaseCurrencyCode()
    {
        // @todo: Rewrite this to DI
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $objectManager->get('\Magento\Directory\Model\Currency');
        return $currency->getCurrencySymbol();
    }
}
