<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderFactory;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttributes implements OptionSourceInterface
{
    const REQUIRED_ATTRIBUTES = ['id', 'sku', 'name'];

    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private SortOrderFactory $sortOrderFactory;
    private bool $onlyIncludeVisible;
    private bool $onlyIncludeVisibleOnFront;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderFactory $sortOrderFactory,
        bool $onlyIncludeVisible = true,
        bool $onlyIncludeVisibleOnFront = true
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderFactory = $sortOrderFactory;
        $this->onlyIncludeVisible = $onlyIncludeVisible;
        $this->onlyIncludeVisibleOnFront = $onlyIncludeVisibleOnFront;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => '']];

        if ($this->onlyIncludeVisible) {
            $this->searchCriteriaBuilder->addFilter('is_visible', 1);
        }

        if ($this->onlyIncludeVisibleOnFront) {
            $this->searchCriteriaBuilder->addFilter('is_visible_on_front', 1);
        }

        $sortOrder = $this->sortOrderFactory->create(['field' => 'attribute_code', 'direction' => 'asc']);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->productAttributeRepository->getList($searchCriteria);
        foreach ($searchResult->getItems() as $productAttribute) {
            if (in_array($productAttribute->getAttributeCode(), self::REQUIRED_ATTRIBUTES)) {
                continue;
            }

            $options[] = [
                'value' => $productAttribute->getAttributeCode(),
                'label' => $productAttribute->getAttributeCode() . ': ' . $productAttribute->getDefaultFrontendLabel(),
                'disabled' => true
            ];
        }

        return $options;
    }
}
