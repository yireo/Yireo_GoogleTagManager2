<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\Api\Data\ProductTagInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\GetCategoryFromProduct;

class ProductDataMapper
{
    private Config $config;
    private GetAttributeValue $getAttributeValue;
    private GetCategoryFromProduct $getCategoryFromProduct;
    private LayoutInterface $layout;

    /**
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     * @param GetCategoryFromProduct $getCategoryFromProduct
     * @param LayoutInterface $layout
     */
    public function __construct(
        Config $config,
        GetAttributeValue $getAttributeValue,
        GetCategoryFromProduct $getCategoryFromProduct,
        LayoutInterface $layout
    ) {
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
        $this->getCategoryFromProduct = $getCategoryFromProduct;
        $this->layout = $layout;
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

        try {
            $productData[$prefix . 'list_id'] = $this->getCategoryFromProduct->get($product)->getId();
            $productData[$prefix . 'list_name'] = $this->getCategoryFromProduct->get($product)->getName();
        } catch (NoSuchEntityException $noSuchEntityException) {
        }

        $productData['price'] = $product->getFinalPrice();
        $productData = $this->parseDataLayerMappingFromLayout($product, $productData);

        // @todo: Add "variant" reference to Configurable Product

        return $productData;
    }

    /**
     * @return string[]
     */
    private function getProductFields(): array
    {
        return array_merge(['id', 'sku', 'name'], $this->config->getProductEavAttributeCodes());
    }

    private function parseDataLayerMappingFromLayout(ProductInterface $product, array $data): array
    {
        $block = $this->getDataLayerBlock();
        $dataLayerMapping = $block->getData('data_layer_mapping');
        if (!isset($dataLayerMapping['product'])) {
            return [];
        }

        foreach ($dataLayerMapping['product'] as $tagName => $tagValue) {
            if (is_string($tagValue) && array_key_exists($tagValue, $data)) {
                $data[$tagName] = $data[$tagValue];
                continue;
            }

            if ($tagValue instanceof ProductTagInterface) {
                $tagValue->setProduct($product);
                $data[$tagName] = $tagValue->get();
                continue;
            }

            if ($tagValue instanceof TagInterface) {
                $data[$tagName] = $tagValue->get();
            }
        }

        return $data;
    }

    /**
     * @return BlockInterface
     */
    private function getDataLayerBlock(): BlockInterface
    {
        return $this->layout->getBlock('yireo_googletagmanager2.data-layer');
    }
}
