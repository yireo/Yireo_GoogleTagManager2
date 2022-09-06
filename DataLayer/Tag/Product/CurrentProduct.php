<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

class CurrentProduct implements ArgumentInterface
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

    public function getId(): int
    {
        return (int)$this->getCurrentProduct->get()->getId();
    }

    public function getName(): string
    {
        return (string)$this->getCurrentProduct->get()->getName();
    }

    public function getSku(): string
    {
        return (string)$this->getCurrentProduct->get()->getSku();
    }

    public function getCreatedAt(): string
    {
        return (string)$this->getCurrentProduct->get()->getCreatedAt();
    }

    public function getTypeId(): string
    {
        return (string)$this->getCurrentProduct->get()->getTypeId();
    }

    public function getAttributeSetId(): int
    {
        return (int)$this->getCurrentProduct->get()->getAttributeSetId();
    }

    public function getPrice(): float
    {
        return (float)$this->getCurrentProduct->get()->getFinalPrice();
    }

    public function getCategoryName()
    {
        $this->productCategory->setProduct($this->getCurrentProduct->get());
        return $this->productCategory->get();
    }
}
