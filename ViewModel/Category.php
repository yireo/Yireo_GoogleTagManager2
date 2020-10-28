<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use Exception;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Category
 */
class Category implements ArgumentInterface
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Category constructor.
     *
     * @param LayoutFactory $layoutFactory
     * @param RequestInterface $request
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        RequestInterface $request
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->request = $request;
    }

    /**
     * @return AbstractCollection|null
     * @throws Exception
     */
    public function getLoadedProductCollection()
    {
        /** @var ListProduct $productListBlock */
        $productListBlock = $this->layoutFactory->create()->getBlock('category.products.list');

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
        /** @var Toolbar $productListBlockToolbar */
        $productListBlockToolbar = $this->layoutFactory->create()->getBlock('product_list_toolbar');
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
        if ($page = (int)$this->request->getParam('p')) {
            return $page;
        }

        return 1;
    }
}
