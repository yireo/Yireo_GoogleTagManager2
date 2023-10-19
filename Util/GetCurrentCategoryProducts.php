<?php declare(strict_types=1);

namespace AdPage\GTM\Util;

use Magento\Catalog\Api\Data\ProductInterface;

class GetCurrentCategoryProducts
{
    private $products = [];

    public function addProduct(ProductInterface $product)
    {
        $this->products[$product->getId()] = $product;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
