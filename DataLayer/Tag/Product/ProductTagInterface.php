<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

interface ProductTagInterface extends TagInterface
{
    public function setProduct(ProductInterface $product);
}
