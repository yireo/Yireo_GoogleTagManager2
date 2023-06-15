<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class GetCategoryFromProduct
{
    private CategoryRepositoryInterface $categoryRepository;
    private CategoryListInterface $categoryListRepository;
    private FilterBuilder $filterBuilder;
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterGroupBuilder $filterGroupBuilder;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryListInterface $categoryListRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryListRepository = $categoryListRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @param ProductInterface $product
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function get(ProductInterface $product): CategoryInterface
    {
        $categoryIds = $product->getCategoryIds();
        $categoryId = array_shift($categoryIds);
        return $this->categoryRepository->get($categoryId);
    }

    /**
     * @param ProductInterface $product
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAll(ProductInterface $product): array
    {
        $categoryIds = $product->getCategoryIds();

        $this->searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $entityIdFilterGroup = $this->filterGroupBuilder->create();
        $entityIdFilterGroup->setFilters([$this->filterBuilder
            ->setField('entity_id')
            ->setConditionType('in')
            ->setValue($categoryIds)
            ->create()]);

        $filterGroup = $this->filterGroupBuilder->create();
        $filterGroup->setFilters([$this->filterBuilder
            ->setField('is_active')
            ->setConditionType('eq')
            ->setValue(1)
            ->create()]);

        $this->searchCriteriaBuilder->setFilterGroups([$entityIdFilterGroup, $filterGroup]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->categoryListRepository->getList($searchCriteria)->getItems();
    }
}
