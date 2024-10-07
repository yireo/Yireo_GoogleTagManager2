<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Model\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CustomerAttributes implements OptionSourceInterface
{
    private AttributeRepositoryInterface $attributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private SortOrderFactory $sortOrderFactory;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderFactory $sortOrderFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderFactory = $sortOrderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => '']];

        $sortOrder = $this->sortOrderFactory->create(['field' => 'attribute_code', 'direction' => 'asc']);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->attributeRepository->getList('customer', $searchCriteria);
        foreach ($searchResult->getItems() as $customerAttribute) {
            /** @var Attribute $customerAttribute */
            if (false === $this->isAttributeDisplayedInFrontend($customerAttribute)) {
                continue;
            }


            $options[] = [
                'value' => $customerAttribute->getAttributeCode(),
                'label' => $customerAttribute->getAttributeCode() . ': ' . $customerAttribute->getDefaultFrontendLabel()
            ];
        }

        return $options;
    }

    /**
     * @param Attribute $attribute
     * @return bool
     */
    private function isAttributeDisplayedInFrontend(Attribute $attribute): bool
    {
        $forms = $attribute->getUsedInForms();
        foreach ($forms as $form) {
            if (preg_match('/^customer_/', $form)) {
                return true;
            }
        }

        return false;
    }
}
