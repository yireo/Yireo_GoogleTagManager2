<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

class CurrentCategoryName implements TagInterface
{
    private GetCurrentProduct $getCurrentProduct;
    private ProductCategory $productCategory;

    /**
     * @param GetCurrentProduct $getCurrentProduct
     * @param ProductCategory $productCategory
     */
    public function __construct(
        GetCurrentProduct $getCurrentProduct,
        ProductCategory $productCategory
    ) {
        $this->getCurrentProduct = $getCurrentProduct;
        $this->productCategory = $productCategory;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function get(): string
    {
        $currentProduct = $this->getCurrentProduct->get();
        return $this->productCategory->setProduct($currentProduct)->get();
    }
}
