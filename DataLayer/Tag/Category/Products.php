<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Yireo\GoogleTagManager2\DataLayer\Tag\AddTagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;
use Yireo\GoogleTagManager2\Util\GetCurrentCategoryProducts;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

class Products implements AddTagInterface
{
    private GetCurrentCategoryProducts $getCurrentCategoryProducts;
    private GetCurrentCategory $getCurrentCategory;
    private ProductDataMapper $productDataMapper;

    public function __construct(
        GetCurrentCategoryProducts $getCurrentCategoryProducts,
        GetCurrentCategory $getCurrentCategory,
        ProductDataMapper $productDataMapper
    ) {
        $this->getCurrentCategoryProducts = $getCurrentCategoryProducts;
        $this->getCurrentCategory = $getCurrentCategory;
        $this->productDataMapper = $productDataMapper;
    }

    public function addData()
    {
        $productDataMapper = [];
        $i = 1;
        foreach ($this->getCurrentCategoryProducts->getProducts() as $product) {
            $product->setCategory($this->getCurrentCategory->get());
            $productDataMapper = $this->productDataMapper->mapByProduct($product);
            $productDataMapper['category'] = $this->getCurrentCategory->get()->getName();
            $productDataMapper['position'] = $i;
            $i++;
        }

        return [
            'products' => $productDataMapper
        ];
    }
}
