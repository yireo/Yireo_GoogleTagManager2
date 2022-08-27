<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

class CurrentProductId implements TagInterface
{
    private GetCurrentProduct $getCurrentProduct;
    
    public function __construct(
        GetCurrentProduct $getCurrentProduct,
    ) {
        $this->getCurrentProduct = $getCurrentProduct;
    }
    
    public function get(): int
    {
        return (int)$this->getCurrentProduct->get()->getId();
    }
}
