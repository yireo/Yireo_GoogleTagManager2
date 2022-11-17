<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

class GetProductsFromCategoryBlock
{
    private LayoutInterface $layout;
    private $productCollection = null;
    private ProductDataMapper $productDataMapper;

    public function __construct(
        LayoutInterface $layout,
        ProductDataMapper $productDataMapper
    ) {
        $this->layout = $layout;
        $this->productDataMapper = $productDataMapper;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getProducts(): array
    {
        $products = [];
        $productCollection = $this->getProductCollectionFromBlock();

        if (empty($productCollection)) {
            return [];
        }

        $page = 1;
        $pageSize = 1;
        if ($this->products) {
            $page = $this->products->getCurPage();
            $pageSize = $this->products->getPageSize();
        }

        $position = ($pageSize * ($page - 1)) + 1;

        /** @var Product $product */
        foreach ($productCollection as $product) {
            $productData = $this->productDataMapper->mapByProduct($product);
            $productData['position'] = $position++;
            $products[] = $productData;
        }

        return $products;
    }

    /**
     * @return Collection|null
     */
    private function getProductCollectionFromBlock(): ?Collection
    {
        if (!empty($this->productCollection)) {
            return $this->productCollection;
        }

        /** @var ListProduct $listBlock */
        $listBlock = $this->layout->getBlock('category.products.list');
        if (!$listBlock) {
            $listBlock = $this->layout->getBlock('search_result_list');
            if (!$listBlock) {
                return null;
            }
        }

        $this->products = $listBlock->getLoadedProductCollection();
        return $this->products;
    }
}
