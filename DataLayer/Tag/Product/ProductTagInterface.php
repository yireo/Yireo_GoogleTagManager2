<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag\Product;

use Magento\Catalog\Model\Product;
use Tagging\GTM\Api\Data\TagInterface;

interface ProductTagInterface extends TagInterface
{
    public function setProduct(Product $product);
}
