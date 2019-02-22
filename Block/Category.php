<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Block;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\LocalizedException;
use Yireo\GoogleTagManager2\ViewModel\Category as CategoryViewModel;

/**
 * Class \Yireo\GoogleTagManager2\Block\Category
 */
class Category extends Generic
{
    /**
     * @var string
     */
    protected $_template = 'category.phtml';

    /**
     * @return CategoryViewModel
     */
    public function getViewModel()
    {
        return $this->getData('view_model');
    }

    /**
     * @return AbstractCollection|null
     * @throws LocalizedException
     */
    public function getLoadedProductCollection()
    {
        /** @var ListProduct $productListBlock */
        $productListBlock = $this->layout->getBlock('category.products.list');

        if (empty($productListBlock)) {
            return null;
        }

        $toolbar = $productListBlock->getToolbarBlock();

        // Fetch the current collection from the block and set pagination
        $collection = $productListBlock->getLoadedProductCollection();

        $collection->setCurPage($this->getCurrentPage());

        if ((int)$this->getLimit()) {
            $collection->setPageSize($this->getLimit());
        }
        // use sortable parameters
        $orders = $productListBlock->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $productListBlock->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $productListBlock->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $productListBlock->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        return $collection;
    }

    /**
     * Return the current page limit, as set by the toolbar block
     *
     * @return int
     */
    private function getLimit()
    {
        /** @var Toolbar $productListBlockToolbar */
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
    private function getCurrentPage()
    {
        if ($page = (int)$this->getRequest()->getParam('p')) {
            return $page;
        }

        return 1;
    }
}
