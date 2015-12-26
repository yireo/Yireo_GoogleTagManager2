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

/**
 * Class \Yireo\GoogleTagManager2\Block\Category
 */
class Category extends Generic
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Yireo\GoogleTagManager2\Helper\Data $helper,
     * @param \Yireo\GoogleTagManager2\Model\Container $container
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Yireo\GoogleTagManager2\Helper\Data $helper,
        \Yireo\GoogleTagManager2\Model\Container $container,
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = []
    ) {
        $this->layout = $layout;

        parent::__construct(
            $context,
            $scopeConfig,
            $storeManager,
            $helper,
            $container,
            $data
        );
    }

    /**
     * @return Mage_Eav_Model_Entity_Collection_Abstract|null
     */
    public function getLoadedProductCollection()
    {
        /** @var Mage_Catalog_Block_Product_List $productListBlock */
        $productListBlock = $this->layout->getBlock('category.products.list');

        if (empty($productListBlock)) {
            return null;
        }

        // Fetch the current collection from the block and set pagination
        $collection = $productListBlock->getLoadedProductCollection();
        $collection->setCurPage($this->getCurrentPage())->setPageSize($this->getLimit());

        return $collection;
    }

    /**
     * Return the current page limit, as set by the toolbar block
     *
     * @return int
     */
    protected function getLimit()
    {
        /** @var Mage_Catalog_Block_Product_List_Toolbar $productListBlockToolbar */
        $productListBlockToolbar = $this->layout->getBlock('product_list_toolbar');
        if (empty($productListBlockToolbar)) {
            return 9;
        }

        return $productListBlockToolbar->getLimit();
    }

    /**
     * Return the current page as set in the URL
     *
     * @return int
     * @throws Exception
     */
    protected function getCurrentPage()
    {
        if ($page = (int) $this->getRequest()->getParam('p')) {
            return $page;
        }

        return 1;
    }
}
