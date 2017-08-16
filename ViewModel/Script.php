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
 * Class \Yireo\GoogleTagManager2\ViewModel\Script
 */
class Script
{
    /**
     * @param \Yireo\GoogleTagManager2\Helper\Data $moduleHelper
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Magento\Sales\Model\Order $salesOrder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Group $customerGroup
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     */
    public function __construct(
        \Yireo\GoogleTagManager2\Helper\Data $moduleHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Group $customerGroup,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->layoutFactory = $layoutFactory;
        $this->blockFactory = $blockFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrder = $salesOrder;
        $this->coreRegistry = $coreRegistry;
        $this->customerGroup = $customerGroup;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Return this header script
     *
     * @return string
     */
    public function getScript()
    {
        $childScript = '';

        // Load the main script
        if (!($block = $this->fetchBlock('generic', 'generic', 'generic.phtml'))) {
            return $childScript;
        }

        // Add product-information
        $this->addProduct($childScript);

        // Add category-information
        $this->addCategory($childScript);

        // Add custom information
        $this->addCustom($childScript);

        $block->setChildScript($childScript);
        $html = $block->toHtml();

        return $html;
    }

    /**
     * @param $childScript string
     */
    public function addProduct(&$childScript)
    {
        $currentProduct = $this->coreRegistry->registry('current_product');
        if (empty($currentProduct)) {
            return;
        }

        $productBlock = $this->fetchBlock('product', 'product', 'product.phtml');
        if (!$productBlock) {
            return;
        }

        $productBlock->setProduct($currentProduct);
        $childScript .= $productBlock->toHtml();
    }

    /**
     * @param $childScript string
     */
    public function addCategory(&$childScript)
    {
        $currentCategory = $this->coreRegistry->registry('current_category');
        if (empty($currentCategory)) {
            return;
        }

        $categoryBlock = $this->fetchBlock('category', 'category', 'category.phtml');
        if (!$categoryBlock) {
            return;
        }

        $categoryBlock->setCategory($currentCategory);
        $childScript .= $categoryBlock->toHtml();
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

    /**
     * Fetch a specific block
     *
     * @param string $className
     * @param string $classType
     * @param string $template
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function fetchBlock($className, $classType, $template)
    {
        if (!strstr($className, '\\')) {
            $className = '\Yireo\GoogleTagManager2\Block\\' . ucfirst($className);
        }

        if (!strstr($classType, '\\')) {
            $classType = '\Yireo\GoogleTagManager2\Block\\' . ucfirst($classType);
        }

        if (!strstr($template, '::')) {
            $template = 'Yireo_GoogleTagManager2::' . $template;
        }

        if ($block = $this->layoutFactory->create()->getBlock($className)) {
            $this->moduleHelper->debug('Helper: Loading block from layout: ' . $className);
            return $block;
        }

        if ($block = $this->blockFactory->createBlock($classType)->setTemplate($template)) {
            $this->moduleHelper->debug('Helper: Creating new block: ' . $classType);
            return $block;
        }

        $this->moduleHelper->debug('Helper: Unknown block: ' . $className);
        throw new \InvalidArgumentException('Helper: Unknown block: ' . $className);
    }

    /**
     * Return whether this module is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->moduleHelper->isEnabled();
    }
}
