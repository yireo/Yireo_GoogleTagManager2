<?php declare(strict_types=1);

namespace AdPage\GTM\Model\Config\Source;

use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CategoryAttributes implements OptionSourceInterface
{
    private CategoryAttributeRepositoryInterface $categoryAttributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private SortOrderFactory $sortOrderFactory;

    public function __construct(
        CategoryAttributeRepositoryInterface $categoryAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderFactory $sortOrderFactory
    ) {
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderFactory = $sortOrderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('')]];

        $this->searchCriteriaBuilder->addFilter('is_visible', 1);
        $sortOrder = $this->sortOrderFactory->create(['field' => 'attribute_code', 'direction' => 'asc']);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->categoryAttributeRepository->getList($searchCriteria);
        foreach ($searchResult->getItems() as $categoryAttribute) {
            $options[] = [
                'value' => $categoryAttribute->getAttributeCode(),
                'label' => $categoryAttribute->getAttributeCode() . ': '.$categoryAttribute->getDefaultFrontendLabel()
            ];
        }

        return $options;
    }
}
