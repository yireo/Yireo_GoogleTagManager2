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

    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }
    /**
     * @return Mage_Eav_Model_Entity_Collection_Abstract|null
     */
    public function getLoadedProductCollection()
    {
        /** @var Mage_Catalog_Block_Product_List $productListBlock */
        $productListBlock = $this->layout->getBlock('product_list');

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
