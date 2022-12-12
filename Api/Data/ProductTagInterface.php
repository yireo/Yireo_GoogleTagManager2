<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api\Data;

use Magento\Catalog\Api\Data\ProductInterface;

interface ProductTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function setProduct(ProductInterface $product);
}
