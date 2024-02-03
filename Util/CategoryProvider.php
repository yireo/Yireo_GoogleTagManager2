<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\Exception\NotUsingSetProductSkusException;

class CategoryProvider
{
    /**
     * @var int[]
     */
    private array $categoryIds = [];
    
    /**
     * @var CategoryInterface[]
     */
    private array $loadedCategories = [];
    
    private CategoryListInterface $categoryListRepository;
    private FilterBuilder $filterBuilder;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterGroupBuilder $filterGroupBuilder;
    private StoreManagerInterface $storeManager;
    
    public function __construct(
        CategoryListInterface $categoryListRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryListRepository = $categoryListRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->storeManager = $storeManager;
    }
    
    /**
     * @param int[] $categoryIds
     * @return void
     * @throws NoSuchEntityException
     */
    public function addCategoryIds(array $categoryIds)
    {
        if (empty($categoryIds)) {
            return;
        }
        
        $rootCategoryId = $this->getRootCategoryId();
        $categoryIds = array_filter($categoryIds, function ($categoryId) use ($rootCategoryId) {
            return (int)$categoryId !== $rootCategoryId;
        });
        
        $this->categoryIds = array_unique(array_merge($this->categoryIds, $categoryIds));
    }
    
    /**
     * @param int $categoryId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $categoryId): CategoryInterface
    {
        foreach ($this->getLoadedCategories() as $category) {
            if ((int)$category->getId() === $categoryId) {
                return $category;
            }
        }
        
        throw new NotUsingSetProductSkusException('Using getCategoryById() delivers no result');
    }
    
    /**
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getLoadedCategories(): array
    {
        if (empty($this->categoryIds)) {
            throw new NotUsingSetProductSkusException('Using getCategories() before setCategoryIds()');
        }
        
        $loadCategoryIds = array_diff($this->categoryIds, array_keys($this->loadedCategories));
        if (count($loadCategoryIds) > 0) {
            foreach ($this->loadCategoriesByIds($loadCategoryIds) as $category) {
                $this->loadedCategories[(int)$category->getId()] = $category;
            }
        }
        
        return array_filter($this->loadedCategories, function (CategoryInterface $category) {
            return $category->getIsActive();
        });
    }
    
    /**
     * @param ProductInterface $product
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getFirstByProduct(ProductInterface $product): CategoryInterface
    {
        $productCategoryIds = $product->getCategoryIds();
        if (empty($productCategoryIds)) {
            throw new NoSuchEntityException(__('Product "' . $product->getSku() . '" has no categories'));
        }
        
        $productCategoryId = array_shift($productCategoryIds);
        $this->addCategoryIds([$productCategoryId]);
        
        return $this->getLoadedCategories()[$productCategoryId];
    }
    
    /**
     * @param ProductInterface $product
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAllByProduct(ProductInterface $product): array
    {
        $productCategoryIds = $product->getCategoryIds();
        if (empty($productCategoryIds)) {
            throw new NoSuchEntityException(__('Product "' . $product->getSku() . '" has no categories'));
        }
        
        $this->addCategoryIds($productCategoryIds);
        
        return array_filter(
            $this->getLoadedCategories(),
            function (CategoryInterface $category) use ($productCategoryIds) {
                return in_array($category->getId(), $productCategoryIds);
            }
        );
    }
    
    /**
     * @param array $categoryIds
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    private function loadCategoriesByIds(array $categoryIds): array
    {
        /** @var FilterGroup $entityIdFilterGroup */
        $entityIdFilterGroup = $this->filterGroupBuilder->create();
        $entityIdFilterGroup->setFilters([
            $this->filterBuilder
                ->setField('entity_id')
                ->setConditionType('in')
                ->setValue($categoryIds)
                ->create(),
        ]);
        
        /** @var FilterGroup $rootCategoryFilterGroup */
        $rootCategoryFilterGroup = $this->filterGroupBuilder->create();
        $rootCategoryFilterGroup->setFilters([
            $this->filterBuilder
                ->setField('path')
                ->setConditionType('like')
                ->setValue('1/' . $this->getRootCategoryId() . '/%')
                ->create(),
        ]);
        
        $this->searchCriteriaBuilder->setFilterGroups([
            $entityIdFilterGroup,
            $rootCategoryFilterGroup,
        ]);
        
        $searchCriteria = $this->searchCriteriaBuilder->create();
        
        return $this->categoryListRepository->getList($searchCriteria)->getItems();
    }
    
    /**
     * @return int
     * @throws NoSuchEntityException
     */
    private function getRootCategoryId(): int
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        return (int)$store->getRootCategoryId();
    }
}
