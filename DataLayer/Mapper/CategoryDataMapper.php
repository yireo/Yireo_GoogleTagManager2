<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Yireo\GoogleTagManager2\Api\Data\CategoryTagInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;

class CategoryDataMapper
{
    private Config $config;
    private GetAttributeValue $getAttributeValue;
    private array $dataLayerMapping;

    /**
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     */
    public function __construct(
        Config $config,
        GetAttributeValue $getAttributeValue,
        array $dataLayerMapping = []
    ) {
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
        $this->dataLayerMapping = $dataLayerMapping;
    }

    /**
     * @param CategoryInterface $category
     * @return array
     * @throws LocalizedException
     */
    public function mapByCategory(CategoryInterface $category): array
    {
        $prefix = 'category_';
        $categoryData = [];
        $categoryFields = $this->getCategoryFields();
        foreach ($categoryFields as $categoryAttributeCode) {
            $attributeValue = $this->getAttributeValue->getCategoryAttributeValue($category, $categoryAttributeCode);
            if (empty($attributeValue)) {
                continue;
            }

            $dataLayerKey = $prefix . $categoryAttributeCode;
            $categoryData[$dataLayerKey] = $attributeValue;
        }

        $categoryData = $this->parseDataLayerMapping($category, $categoryData);

        return $categoryData;
    }

    /**
     * @return string[]
     */
    private function getCategoryFields(): array
    {
        return array_unique(array_merge(['id', 'name'], $this->config->getCategoryEavAttributeCodes()));
    }

    /**
     * @param CategoryInterface $category
     * @param array             $data
     *
     * @return array
     */
    private function parseDataLayerMapping(CategoryInterface $category, array $data): array
    {
        if (empty($this->dataLayerMapping)) {
            return $data;
        }

        foreach ($this->dataLayerMapping as $tagName => $tagValue) {
            if (is_string($tagValue) && array_key_exists($tagValue, $data)) {
                $data[$tagName] = $data[$tagValue];
                continue;
            }

            if ($tagValue instanceof CategoryTagInterface) {
                $tagValue->setCategory($category);
                $data[$tagName] = $tagValue->get();
                continue;
            }

            if ($tagValue instanceof TagInterface) {
                $data[$tagName] = $tagValue->get();
            }
        }

        return $data;
    }
}
