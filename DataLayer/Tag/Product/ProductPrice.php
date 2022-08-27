<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Magento\Catalog\Api\Data\ProductInterface;

class ProductPrice implements ProductTagInterface
{
    private ProductInterface $product;

    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }

    public function get(): float
    {
        return $this->product->getFinalPrice();
    }
}
