<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Tagging\GTM\Util\CategoryProvider;

class ProductCategory implements ProductTagInterface
{
    private Product $product;
    private CategoryProvider $categoryProvider;

    /**
     * @param CategoryProvider $categoryProvider
     */
    public function __construct(
        CategoryProvider $categoryProvider
    ) {
        $this->categoryProvider = $categoryProvider;
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
            return $this->categoryProvider->getFirstByProduct($this->product)->getName();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }
}
