<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Tagging\GTM\Api\Data\TagInterface;
use Tagging\GTM\Util\GetCurrentProduct;

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
