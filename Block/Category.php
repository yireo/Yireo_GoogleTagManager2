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
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|null
     */
    public function getLoadedProductCollection()
    {
        /** @var \Magento\Catalog\Block\Product\ListProduct $productListBlock */
        $productListBlock = $this->layout->getBlock('category.products.list');

        if (empty($productListBlock)) {
            return null;
        }

        // Fetch the current collection from the block and set pagination
        $collection = $productListBlock->getLoadedProductCollection();
        $collection->setCurPage($this->getCurrentPage());
        if((int) $this->getLimit()) {
            $collection->setPageSize($this->getLimit());
        }

        return $collection;
    }

    /**
     * Return the current page limit, as set by the toolbar block
     *
     * @return int
     */
    protected function getLimit()
    {
        /** @var \Magento\Catalog\Block\Product\ProductList\Toolbar $productListBlockToolbar */
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
     * @throws \Exception
     */
    protected function getCurrentPage()
    {
        if ($page = (int) $this->getRequest()->getParam('p')) {
            return $page;
        }

        return 1;
    }
}
