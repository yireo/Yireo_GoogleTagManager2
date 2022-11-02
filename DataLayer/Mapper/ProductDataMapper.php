<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\GetCategoryFromProduct;

class ProductDataMapper
{
    private Config $config;
    private GetAttributeValue $getAttributeValue;
    private GetCategoryFromProduct $getCategoryFromProduct;

    /**
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     * @param GetCategoryFromProduct $getCategoryFromProduct
     */
    public function __construct(
        Config $config,
        GetAttributeValue $getAttributeValue,
        GetCategoryFromProduct $getCategoryFromProduct
    ) {
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
        $this->getCategoryFromProduct = $getCategoryFromProduct;
    }

    /**
     * @param ProductInterface $product
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mapByProduct(ProductInterface $product): array
    {
        $prefix = 'item_';
        $productData = [];
        $productFields = $this->getProductFields();
        foreach ($productFields as $productAttributeCode) {
            $dataLayerKey = $prefix . $productAttributeCode;
            $attributeValue = $this->getAttributeValue->getProductAttributeValue($product, $productAttributeCode);
            if ($attributeValue === null) {
                continue;
            }

            $productData[$dataLayerKey] = $attributeValue;
        }

        $productData[$prefix . 'list_id'] = $this->getCategoryFromProduct->get($product)->getId();
        $productData[$prefix . 'list_name'] = $this->getCategoryFromProduct->get($product)->getName();
        $productData['price'] = $product->getFinalPrice();

        // @todo: Add "variant" reference to Configurable Product
        // @todo: Add "brand" reference to manufacturer

        return $productData;
    }

    /**
     * @return string[]
     */
    private function getProductFields(): array
    {
        return array_merge(['id', 'sku', 'name'], $this->config->getProductEavAttributeCodes());
    }
}
