<?php declare(strict_types=1);

namespace AdPage\GTM\Model\Config\Source;

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

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderFactory $sortOrderFactory
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
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
        $this->searchCriteriaBuilder->addFilter('is_visible_on_front', 1);
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
