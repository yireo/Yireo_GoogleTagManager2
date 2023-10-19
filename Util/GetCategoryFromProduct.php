<?php declare(strict_types=1);

namespace AdPage\GTM\Util;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class GetCategoryFromProduct
{
    private CategoryRepositoryInterface $categoryRepository;
    private CategoryListInterface $categoryListRepository;
    private FilterBuilder $filterBuilder;
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterGroupBuilder $filterGroupBuilder;
    private StoreManagerInterface $storeManager;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryListInterface $categoryListRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryListRepository = $categoryListRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Product $product
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function get(Product $product): CategoryInterface
    {
        $categoryIds = $product->getCategoryIds();
        $categoryId = array_shift($categoryIds);
        return $this->categoryRepository->get($categoryId);
    }

    /**
     * @param Product $product
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAll(Product $product): array
    {
        $categoryIds = $product->getCategoryIds();

        $this->searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        /** @var FilterGroup $entityIdFilterGroup */
        $entityIdFilterGroup = $this->filterGroupBuilder->create();
        $entityIdFilterGroup->setFilters([
            $this->filterBuilder
                ->setField('entity_id')
                ->setConditionType('in')
                ->setValue($categoryIds)
                ->create()
        ]);

        /** @var FilterGroup $isActiveFilterGroup */
        $isActiveFilterGroup = $this->filterGroupBuilder->create();
        $isActiveFilterGroup->setFilters([
            $this->filterBuilder
                ->setField('is_active')
                ->setConditionType('eq')
                ->setValue(1)
                ->create()
        ]);

        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $rootCategoryId = $store->getRootCategoryId();

        /** @var FilterGroup $rootCategoryFilterGroup */
        $rootCategoryFilterGroup = $this->filterGroupBuilder->create();
        $rootCategoryFilterGroup->setFilters([
            $this->filterBuilder
                ->setField('path')
                ->setConditionType('like')
                ->setValue('1/' . $rootCategoryId . '/%')
                ->create()
        ]);

        $this->searchCriteriaBuilder->setFilterGroups([
            $entityIdFilterGroup,
            $isActiveFilterGroup,
            $rootCategoryFilterGroup
        ]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->categoryListRepository->getList($searchCriteria)->getItems();
    }
}
