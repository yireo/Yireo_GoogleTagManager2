<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

class CurrentProducts implements TagInterface
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
    
    public function get(): array
    {
        $product = $this->getCurrentProduct->get();
        
        return [
            $this->productDataMapper->mapByProduct($product)
        ];
    }
}
