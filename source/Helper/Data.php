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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Group $customerGroup
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Group $customerGroup,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    )
    {
        $this->layoutFactory = $layoutFactory;
        $this->blockFactory = $blockFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrder = $salesOrder;
        $this->coreRegistry = $coreRegistry;
        $this->customerGroup = $customerGroup;
        $this->pricingHelper = $pricingHelper;

        parent::__construct($context);
    }

    /**
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
     * Fetch a specific block
     *
     * @param $name
     * @param $type
     * @param $template
     *
     * @return bool
     */
    public function fetchBlock($name, $type, $template)
    {
        if ($block = $this->layoutFactory->create()->getBlock('\Yireo\GoogleTagManager2\Block\\' . ucfirst($name))) {
            $this->debug('Helper: Loading block from layout: ' . $name);
            return $block;
        }

        if ($block = $this->blockFactory->createBlock('\Yireo\GoogleTagManager2\Block\\' . ucfirst($type))->setTemplate('Yireo_GoogleTagManager2::' . $template)) {
            $this->debug('Helper: Creating new block: ' . $type);
            return $block;
        }

        $this->debug('Helper: Unknown block: ' . $name);

        return false;
    }

    /**
     *
     */
    public function getBaseCurrencyCode()
    {
        // @todo: Rewrite this to DI
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $objectManager->get('\Magento\Directory\Model\Currency');
        return $currency->getCurrencySymbol();
    }

    /**
     * Return this header script
     *
     * @return string
     */
    public function getHeaderScript()
    {
        $childScript = '';

        // Load the main script
        if (!($block = $this->fetchBlock('generic', 'generic', 'generic.phtml'))) {
            return $childScript;
        }

        // Add customer-information
        $this->addCustomer($childScript);

        // Add product-information
        $this->addProduct($childScript);

        // Add category-information
        $this->addCategory($childScript);

        // Add order-information
        $lastOrderId = $this->checkoutSession->getLastRealOrderId();
        if (!empty($lastOrderId)) {
            $this->addOrder($childScript);

            // Add quote-information
        } else {
            $this->addQuote($childScript);
        }

        // Add custom information
        $this->addCustom($childScript);

        $block->setChildScript($childScript);
        $html = $block->toHtml();

        return $html;
    }

    /**
     * @param $childScript string
     */
    public function addCustomer(&$childScript)
    {
        $customer = $this->customerSession->getCustomer();

        if (!empty($customer)) {
            $customerBlock = $this->fetchBlock('customer', 'customer', 'customer.phtml');

            if ($customerBlock) {
                $customerBlock->setCustomer($customer);

                $customerGroup = $this->customerGroup->load($customer->getGroupId());
                $customerBlock->setCustomerGroup($customerGroup);

                $childScript .= $customerBlock->toHtml();
            }
        }
    }

    /**
     * @param $childScript string
     */
    public function addProduct(&$childScript)
    {
        $currentProduct = $this->coreRegistry->registry('current_product');

        if (!empty($currentProduct)) {
            $productBlock = $this->fetchBlock('product', 'product', 'product.phtml');

            if ($productBlock) {
                $productBlock->setProduct($currentProduct);
                $childScript .= $productBlock->toHtml();
            }
        }
    }

    /**
     * @param $childScript string
     */
    public function addCategory(&$childScript)
    {
        $currentCategory = $this->coreRegistry->registry('current_category');

        if (!empty($currentCategory)) {
            $categoryBlock = $this->fetchBlock('category', 'category', 'category.phtml');

            if ($categoryBlock) {
                $categoryBlock->setCategory($currentCategory);
                $childScript .= $categoryBlock->toHtml();
            }
        }
    }

    /**
     * @param $childScript string
     */
    public function addOrder(&$childScript)
    {
        $lastOrderId = $this->checkoutSession->getLastRealOrderId();

        if (!empty($lastOrderId)) {
            $order = $this->salesOrder->loadByIncrementId($lastOrderId);
            $orderBlock = $this->fetchBlock('order', 'order', 'order.phtml');

            if ($orderBlock) {
                $orderBlock->setOrder($order);
                $childScript .= $orderBlock->toHtml();
            }
        }
    }

    /**
     * @param $childScript string
     */
    public function addQuote(&$childScript)
    {
        $quote = $this->checkoutSession->getQuote();

        if ($quote->getId() > 0) {
            $quoteBlock = $this->fetchBlock('quote', 'quote', 'quote.phtml');

            if ($quoteBlock) {
                $quoteBlock->setQuote($quote);
                $childScript .= $quoteBlock->toHtml();
            }
        }
    }

    /**
     * @param $childScript string
     */
    public function addCustom(&$childScript)
    {
        $customBlock = $this->fetchBlock('custom', 'custom', 'custom.phtml');

        if ($customBlock) {
            $childScript .= $customBlock->toHtml();
        }
    }
}
