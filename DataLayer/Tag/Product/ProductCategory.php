<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Util\GetCategoryFromProduct;

class ProductCategory implements ProductTagInterface
{
    private ProductInterface $product;
    private GetCategoryFromProduct $getCategoryFromProduct;

    public function __construct(
        GetCategoryFromProduct $getCategoryFromProduct
    ) {
        $this->getCategoryFromProduct = $getCategoryFromProduct;
    }

    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }

    public function get(): string
    {
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
