<?php declare(strict_types=1);
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\Api\AttributesViewModelInterface;
use Yireo\GoogleTagManager2\Api\CategoryViewModelInterface;

/**
 * Class \Yireo\GoogleTagManager2\ViewModel\Category
 */
class Category implements ArgumentInterface, CategoryViewModelInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var AttributesViewModelInterface
     */
    private $attributesViewModel;

    /**
     * Category constructor.
     *
     * @param RequestInterface $request
     * @param CategoryRepositoryInterface $categoryRepository
     * @param AttributesViewModelInterface $attributesViewModel
     */
    public function __construct(
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        AttributesViewModelInterface $attributesViewModel
    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->attributesViewModel = $attributesViewModel;
    }

    /**
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getCurrentCategory(): CategoryInterface
    {
        return $this->categoryRepository->get((int)$this->request->getParam('id'));
    }

    /**
     * @param CategoryInterface $category
     */
    public function addCategory(CategoryInterface $category)
    {
        $attributes = $this->mapCategoryAttributes($category);
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->attributesViewModel->addAttribute($attributeName, $attributeValue);
        }
    }

    /**
     * @param ProductInterface $product
     */
    public function addCategoryProduct(ProductInterface $product)
    {
        $categoryProducts = $this->attributesViewModel->getAttribute('categoryProducts', []);
        $categoryProducts[$product->getId()] = $this->mapProductAttributes($product);
        $this->attributesViewModel->addAttribute('categoryProducts', $categoryProducts);
    }

    /**
     * @param int $categorySize
     */
    public function setCategorySize(int $categorySize)
    {
        $this->attributesViewModel->addAttribute('categorySize', $categorySize);
    }

    /**
     * @param CategoryInterface $category
     * @return array
     */
    public function mapCategoryAttributes(CategoryInterface $category): array
    {
        return [
            'categoryId' => $category->getId(),
            'categoryName' => $category->getName()
        ];
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function mapProductAttributes(ProductInterface $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice()
        ];
    }
}
