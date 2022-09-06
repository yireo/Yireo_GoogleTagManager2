<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

class CurrentProductName implements TagInterface
{
    private GetCurrentProduct $getCurrentProduct;
    
    public function __construct(
        GetCurrentProduct $getCurrentProduct,
    ) {
        $this->getCurrentProduct = $getCurrentProduct;
    }
    
    public function get(): string
    {
        return $this->getCurrentProduct->get()->getName();
    }
}
