<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\ProductCategory;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\CamelCase;

class ProductDataMapper
{
    private ProductCategory $productCategory;
    private Config $config;
    private CamelCase $camelCase;
    private GetAttributeValue $getAttributeValue;

    /**
     * @param ProductCategory $productCategory
     * @param Config $config
     * @param CamelCase $camelCase
     * @param GetAttributeValue $getAttributeValue
     */
    public function __construct(
        ProductCategory $productCategory,
        Config $config,
        CamelCase $camelCase,
        GetAttributeValue $getAttributeValue
    ) {
        $this->productCategory = $productCategory;
        $this->config = $config;
        $this->camelCase = $camelCase;
        $this->getAttributeValue = $getAttributeValue;
    }

    /**
     * @param ProductInterface $product
     * @param string $prefix
     * @return array
     */
    public function mapByProduct(ProductInterface $product, string $prefix = ''): array
    {
        $productData = [];
        $productFields = $this->getProductFields();
        foreach ($productFields as $productAttributeCode) {
            $dataLayerKey = lcfirst($prefix . $this->camelCase->to($productAttributeCode));
            $attributeValue = $this->getAttributeValue->getProductAttributeValue($product, $productAttributeCode);
            if (empty($attributeValue)) {
                continue;
            }

            $productData[$dataLayerKey] = $attributeValue;
        }

        $productData['category'] = $this->productCategory->setProduct($product)->get();
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
        return array_merge(['id', 'sku', 'name'], $this->config->getProductEavAttributeCodes());
    }
}
