<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\CategoryInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\CamelCase;

class CategoryDataMapper
{
    private CamelCase $camelCase;
    private Config $config;
    private GetAttributeValue $getAttributeValue;

    /**
     * @param CamelCase $camelCase
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     */
    public function __construct(
        CamelCase $camelCase,
        Config $config,
        GetAttributeValue $getAttributeValue
    ) {
        $this->camelCase = $camelCase;
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
    }

    /**
     * @param CategoryInterface $category
     * @param string $prefix
     * @return array
     */
    public function mapByCategory(CategoryInterface $category): array
    {
        $prefix = 'category_';
        $categoryData = [];
        $categoryFields = $this->getCategoryFields();
        foreach ($categoryFields as $categoryAttributeCode) {
            $dataLayerKey = $prefix . $categoryAttributeCode;
            $attributeValue = $this->getAttributeValue->getCategoryAttributeValue($category, $categoryAttributeCode);
            if (empty($attributeValue)) {
                continue;
            }

            $categoryData[$dataLayerKey] = $attributeValue;
        }

        return $categoryData;
    }

    /**
     * @return string[]
     */
    private function getCategoryFields(): array
    {
        return array_filter(array_merge(['id', 'name'], $this->config->getCategoryEavAttributeCodes()));
    }
}
