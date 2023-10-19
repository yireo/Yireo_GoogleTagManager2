<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\Util\GetCategoryFromProduct;

class ProductCategory implements ProductTagInterface
{
    private Product $product;
    private GetCategoryFromProduct $getCategoryFromProduct;

    /**
     * @param GetCategoryFromProduct $getCategoryFromProduct
     */
    public function __construct(
        GetCategoryFromProduct $getCategoryFromProduct
    ) {
        $this->getCategoryFromProduct = $getCategoryFromProduct;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product): ProductCategory
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        /** @var Category|null $category */
        $category = $this->product->getCategory();
        if (is_object($category) && $category instanceof CategoryInterface) {
            return $category->getName();
        }

        try {
            return $this->getCategoryFromProduct->get($this->product)->getName();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }
}
