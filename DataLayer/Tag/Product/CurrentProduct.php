<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Yireo\GoogleTagManager2\DataLayer\Tag\MergeTagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

class CurrentProduct implements MergeTagInterface
{
    private GetCurrentProduct $getCurrentProduct;
    private ProductDataMapper $productDataMapper;

    public function __construct(
        GetCurrentProduct $getCurrentProduct,
        ProductDataMapper $productDataMapper
    ) {
        $this->getCurrentProduct = $getCurrentProduct;
        $this->productDataMapper = $productDataMapper;
    }

    public function mergeData(): array
    {
        $product = $this->getCurrentProduct->get();
        return $this->productDataMapper->mapByProduct($product);
    }
}
