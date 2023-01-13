<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\ProductTagInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\GetCategoryFromProduct;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class ProductDataMapper
{
    private Config $config;
    private GetAttributeValue $getAttributeValue;
    private GetCategoryFromProduct $getCategoryFromProduct;
    private PriceFormatter $priceFormatter;
    private array $dataLayerMapping;

    /**
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     * @param GetCategoryFromProduct $getCategoryFromProduct
     * @param array $dataLayerMapping
     */
    public function __construct(
        Config $config,
        GetAttributeValue $getAttributeValue,
        GetCategoryFromProduct $getCategoryFromProduct,
        PriceFormatter $priceFormatter,
        array $dataLayerMapping = []
    ) {
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
        $this->getCategoryFromProduct = $getCategoryFromProduct;
        $this->priceFormatter = $priceFormatter;
        $this->dataLayerMapping = $dataLayerMapping;
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
        $productData['item_id'] = $this->getAttributeValue->getProductAttributeValue($product, 'sku');

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

        $productData['price'] = $this->priceFormatter->format($product->getFinalPrice());
        $productData = $this->attachCategoriesData($product, $productData);
        $productData = $this->parseDataLayerMapping($product, $productData);

        // @todo: Add "variant" reference to Configurable Product

        return $productData;
    }

    /**
     * @return string[]
     */
    private function getProductFields(): array
    {
        return array_merge(['name'], $this->config->getProductEavAttributeCodes());
    }

    /**
     * @param ProductInterface $product
     * @param array $data
     * @return array
     */
    private function attachCategoriesData(ProductInterface $product, array $data): array
    {
        $maxCategoriesCount = 5;
        $currentCategoriesCount = 1;

        foreach ($this->getCategoryFromProduct->getAll($product) as $category) {
            if ($category->getParentId() == 1) {
                continue;
            }

            $key = 'item_category' . ($currentCategoriesCount == 1 ? '' : $currentCategoriesCount++);
            $data[$key] = $category->getName();

            if ($currentCategoriesCount > $maxCategoriesCount) {
                break;
            }
        }

        return $data;
    }

    /**
     * @param ProductInterface $product
     * @param array $data
     * @return array
     */
    private function parseDataLayerMapping(ProductInterface $product, array $data): array
    {
        if (empty($this->dataLayerMapping)) {
            return [];
        }

        foreach ($this->dataLayerMapping as $tagName => $tagValue) {
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
}
