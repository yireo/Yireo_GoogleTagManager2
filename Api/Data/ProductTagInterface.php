<?php declare(strict_types=1);

namespace Tagging\GTM\Api\Data;

use Magento\Catalog\Api\Data\ProductInterface;

interface ProductTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function setProduct(ProductInterface $product);
}
