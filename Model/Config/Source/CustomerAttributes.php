<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Model\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class CustomerAttributes implements OptionSourceInterface
{
    private AttributeRepositoryInterface $attributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('')]];

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult = $this->attributeRepository->getList('customer', $searchCriteria);
        foreach ($searchResult->getItems() as $customerAttribute) {
            $options[] = [
                'value' => $customerAttribute->getAttributeCode(),
                'label' => $customerAttribute->getAttributeCode() . ': ' . $customerAttribute->getDefaultFrontendLabel()
            ];
        }

        return $options;
    }
}
