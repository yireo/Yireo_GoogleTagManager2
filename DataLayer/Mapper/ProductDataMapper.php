<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\ProductCategory;

class ProductDataMapper
{
    private ProductCategory $productCategory;

    /**
     * @param ProductCategory $productCategory
     */
    public function __construct(
        ProductCategory $productCategory,
    ) {
        $this->productCategory = $productCategory;
    }

    /**
     * @param ProductInterface $product
     * @param string $prefix
     * @return array
     */
    public function mapByProduct(ProductInterface $product, string $prefix = ''): array
    {
        $productData = [];
        foreach ($this->getProductFields() as $attributeName => $dataLayerKey) {
            if ($prefix) {
                $dataLayerKey = $prefix . ucfirst($dataLayerKey);
            }

            // @todo: Add support here for Extension Attributes
            // @todo: Add support for EAV values (like with color IDs)
            $productData[$dataLayerKey] = $product->getData($attributeName);
        }

        $this->productCategory->setProduct($product);
        $productData['category'] = $this->productCategory->get();
        $productData['price'] = $product->getFinalPrice();

        // @todo: Add "variant" reference to Configurable Product
        // @todo: Add "brand" reference to manufacturer

        return $productData;
    }

    /**
     * @return string[]
     */
    public function getProductFields(): array
    {
        return [
            'entity_id' => 'id',
            'name' => 'name',
            'sku' => 'sku',
        ];
    }
}
