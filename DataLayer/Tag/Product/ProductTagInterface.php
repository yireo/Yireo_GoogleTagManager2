<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Product;

use Magento\Catalog\Model\Product;
use AdPage\GTM\Api\Data\TagInterface;

interface ProductTagInterface extends TagInterface
{
    public function setProduct(Product $product);
}
