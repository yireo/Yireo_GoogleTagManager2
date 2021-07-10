<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api;

use Magento\Catalog\Api\Data\ProductInterface;

interface ProductViewModelInterface
{
    /**
     * @return ProductInterface
     */
    public function getCurrentProduct(): ProductInterface;

    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function addProduct(ProductInterface $product);

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function mapProductAttributes(ProductInterface $product): array;

    /**
     * @param ProductInterface $product
     * @return float
     */
    public function getProductPrice(ProductInterface $product): float;
}
