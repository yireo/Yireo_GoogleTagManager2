<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2019 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use InvalidArgumentException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Yireo\GoogleTagManager2\Helper\Data as DataHelper;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Script
 */
class Script implements ArgumentInterface
{
    /**
     * @var DataHelper
     */
    private $moduleHelper;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Order
     */
    private $salesOrder;

    /**
     * @var CustomerGroup
     */
    private $customerGroup;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetCurrentProduct
     */
    private $getCurrentProduct;

    /**
     * @var GetCurrentCategory
     */
    private $getCurrentCategory;

    /**
     * @param DataHelper $moduleHelper
     * @param LayoutInterface $layout
     * @param BlockFactory $blockFactory
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Order $salesOrder
     * @param CustomerGroup $customerGroup
     * @param PricingHelper $pricingHelper
     * @param Config $config
     * @param GetCurrentProduct $getCurrentProduct
     * @param GetCurrentCategory $getCurrentCategory
     */
    public function __construct(
        DataHelper $moduleHelper,
        LayoutInterface $layout,
        BlockFactory $blockFactory,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Order $salesOrder,
        CustomerGroup $customerGroup,
        PricingHelper $pricingHelper,
        Config $config,
        GetCurrentProduct $getCurrentProduct,
        GetCurrentCategory $getCurrentCategory
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->layout = $layout;
        $this->blockFactory = $blockFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrder = $salesOrder;
        $this->customerGroup = $customerGroup;
        $this->pricingHelper = $pricingHelper;
        $this->config = $config;
        $this->getCurrentProduct = $getCurrentProduct;
        $this->getCurrentCategory = $getCurrentCategory;
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
        try {
            $currentProduct = $this->getCurrentProduct->get();
        } catch (NoSuchEntityException $e) {
            return null;
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
        try {
            $currentCategory = $this->getCurrentCategory->get();
        } catch (NoSuchEntityException $e) {
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
     * @return BlockInterface
     */
    public function fetchBlock($blockName, $classType, $template)
    {
        $blockName = 'googletagmanager_' . $blockName;

        if (!strstr($classType, '\\')) {
            $classType = '\Yireo\GoogleTagManager2\Block\\' . ucfirst($classType);
        }

        if (!strstr($template, '::')) {
            $template = 'Yireo_GoogleTagManager2::' . $template;
        }

        if ($block = $this->layout->getBlock($blockName)) {
            $this->moduleHelper->debug('Helper: Loading block from layout: ' . $blockName);
            return $block;
        }

        $arguments = ['data' => ['view_model' => Generic::class]];
        if ($block = $this->blockFactory->createBlock($classType, $arguments)->setTemplate($template)) {
            $this->moduleHelper->debug('Helper: Creating new block: ' . $classType);
            return $block;
        }

        $this->moduleHelper->debug('Helper: Unknown block: ' . $blockName);
        throw new InvalidArgumentException('Helper: Unknown block: ' . $blockName);
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
