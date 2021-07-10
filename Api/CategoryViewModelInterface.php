<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

interface CategoryViewModelInterface
{
    /**
     * @return CategoryInterface
     */
    public function getCurrentCategory(): CategoryInterface;

    /**
     * @param CategoryInterface $category
     * @return mixed
     */
    public function addCategory(CategoryInterface $category);

    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function addCategoryProduct(ProductInterface $product);

    /**
     * @param int $categorySize
     * @return mixed
     */
    public function setCategorySize(int $categorySize);

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function mapProductAttributes(ProductInterface $product): array;
}
