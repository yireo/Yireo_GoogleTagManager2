<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

class CurrentProductCategory implements TagInterface
{
    private ProductCategory $productCategory;
    private GetCurrentProduct $getCurrentProduct;

    public function __construct(
        ProductCategory $productCategory,
        GetCurrentProduct $getCurrentProduct
    ) {
        $this->productCategory = $productCategory;
        $this->getCurrentProduct = $getCurrentProduct;
    }


    public function get()
    {
        $this->productCategory->setProduct($this->getCurrentProduct->get());
        return $this->productCategory->get();
    }
}
