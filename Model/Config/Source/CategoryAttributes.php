<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Model\Config\Source;

use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class CategoryAttributes implements OptionSourceInterface
{
    private CategoryAttributeRepositoryInterface $categoryAttributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        CategoryAttributeRepositoryInterface $categoryAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('')]];

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
