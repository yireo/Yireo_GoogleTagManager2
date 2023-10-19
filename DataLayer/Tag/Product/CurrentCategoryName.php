<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\Api\Data\TagInterface;
use AdPage\GTM\Util\GetCurrentProduct;

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
