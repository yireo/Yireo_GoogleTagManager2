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
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order $salesOrder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Group $customerGroup
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     */
    public function __construct(
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

    /**
     * Fetch a specific block
     *
     * @param string $className
     * @param string $classType
     * @param string $template
     *
     * @return \Magento\Framework\View\Element\Template
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
            $this->debug('Helper: Loading block from layout: ' . $className);
            return $block;
        }

        if ($block = $this->blockFactory->createBlock($classType)->setTemplate('Yireo_GoogleTagManager2::' . $template)) {
            $this->debug('Helper: Creating new block: ' . $classType);
            return $block;
        }

        $this->debug('Helper: Unknown block: ' . $className);
        throw new \InvalidArgumentException('Helper: Unknown block: ' . $className);
    }
}
