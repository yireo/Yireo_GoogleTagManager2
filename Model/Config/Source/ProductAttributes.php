<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Model\Config\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttributes implements OptionSourceInterface
{
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('')]];

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult = $this->productAttributeRepository->getList($searchCriteria);
        foreach ($searchResult->getItems() as $productAttribute) {
            $options[] = [
                'value' => $productAttribute->getAttributeCode(),
                'label' => $productAttribute->getAttributeCode() . ': '.$productAttribute->getDefaultFrontendLabel()
            ];
        }

        return $options;
    }
}
