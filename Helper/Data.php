<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Helper;

use Magento\Checkout\Model\Session\Proxy;
use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context as HelperContext;
use Magento\Framework\Pricing\Helper\Data as DataHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \Yireo\GoogleTagManager2\Helper\Data
 */
class Data extends AbstractHelper
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
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var Proxy
     */
    private $checkoutSession;

    /**
     * @var Order
     */
    private $salesOrder;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var DataHelper
     */
    private $pricingHelper;
    /**
     * @var CookieHelper
     */
    private $cookieHelper;

    /**
     * Data constructor.
     *
     * @param HelperContext $context
     * @param LayoutFactory $layoutFactory
     * @param BlockFactory $blockFactory
     * @param Proxy $checkoutSession
     * @param Order $salesOrder
     * @param Registry $coreRegistry
     * @param DataHelper $pricingHelper
     * @param CookieHelper $cookieHelper
     */
    public function __construct(
        HelperContext $context,
        LayoutFactory $layoutFactory,
        BlockFactory $blockFactory,
        Proxy $checkoutSession,
        Order $salesOrder,
        Registry $coreRegistry,
        DataHelper $pricingHelper,
        CookieHelper $cookieHelper
    ) {
        parent::__construct($context);

        $this->layoutFactory = $layoutFactory;
        $this->blockFactory = $blockFactory;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrder = $salesOrder;
        $this->coreRegistry = $coreRegistry;
        $this->pricingHelper = $pricingHelper;
        $this->cookieHelper = $cookieHelper;
    }

    /**
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        $enabled = (bool)$this->getConfigValue('enabled', false);
        if (!$enabled) {
            return false;
        }

        if ($this->cookieHelper->isCookieRestrictionModeEnabled()) {
            return !$this->cookieHelper->isUserNotAllowSaveCookie();
        }

        return true;
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
            ScopeInterface::SCOPE_STORE
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
}
