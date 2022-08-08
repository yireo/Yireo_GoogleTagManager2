<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\CategoryInterface;

class CategoryDataMapper
{
    /**
     * @param CategoryInterface $category
     * @param string $prefix
     * @return array
     */
    public function mapByCategory(CategoryInterface $category, string $prefix = ''): array
    {
        $categoryData = [];
        foreach ($this->getCategoryFields() as $attributeName => $dataLayerKey) {
            if ($prefix) {
                $dataLayerKey = $prefix . ucfirst($dataLayerKey);
            }
            $categoryData[$dataLayerKey] = $category->getData($attributeName);
        }

        return $categoryData;
    }

    /**
     * @return string[]
     */
    public function getCategoryFields(): array
    {
        return [
            'entity_id' => 'id',
            'name' => 'name',
        ];
    }
}
