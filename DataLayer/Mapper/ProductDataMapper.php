<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\ProductTagInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Model\Config\Source\ProductListValue;
use Yireo\GoogleTagManager2\Util\Attribute\GetAttributeValue;
use Yireo\GoogleTagManager2\Util\CategoryProvider;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class ProductDataMapper
{
    private Config $config;
    private GetAttributeValue $getAttributeValue;
    private CategoryProvider $categoryProvider;
    private PriceFormatter $priceFormatter;

    private array $dataLayerMapping;

    private int $counter = 0;

    /**
     * @param Config $config
     * @param GetAttributeValue $getAttributeValue
     * @param CategoryProvider $categoryProvider
     * @param PriceFormatter $priceFormatter
     * @param array $dataLayerMapping
     */
    public function __construct(
        Config $config,
        GetAttributeValue $getAttributeValue,
        CategoryProvider $categoryProvider,
        PriceFormatter $priceFormatter,
        array $dataLayerMapping = []
    ) {
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
        $this->categoryProvider = $categoryProvider;
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
        /** @var Product $product */
        $prefix = 'item_';
        $productData = [];
        $productData['item_id'] = $product->getSku();
        $productData['item_sku'] = $product->getSku();
        $productData['magento_sku'] = $product->getSku();
        $productData['magento_id'] = $product->getId();

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
            $category = $this->getProductCategory($product);
            $productData[$prefix . 'list_id'] = $category->getId();
            $productData[$prefix . 'list_name'] = $category->getName();
        } catch (NoSuchEntityException $noSuchEntityException) {
        }

        $productData['price'] = $this->priceFormatter->format((float)$product->getFinalPrice());

        $productData = $this->attachCategoriesData($product, $productData);
        $productData = $this->parseDataLayerMapping($product, $productData);
        $productData['index'] = $this->counter++;

        // @todo: Add "variant" reference to Configurable Product

        return $productData;
    }

    /**
     * @param ProductInterface $product
     * @return CategoryInterface
     */
    private function getProductCategory($product)
    {
        /** @var Product $product */
        if ($this->config->getProductListValueOnCategory() == ProductListValue::CURRENT_CATEGORY
            && $product->getCategory()
        ) {
            return $product->getCategory();
        }
        return $this->categoryProvider->getFirstByProduct($product);
    }

    /**
     * @return string[]
     */
    private function getProductFields(): array
    {
        return array_filter(array_merge(['name'], $this->config->getProductEavAttributeCodes()));
    }

    /**
     * @param ProductInterface $product
     * @param array $data
     * @return array
     */
    private function attachCategoriesData(ProductInterface $product, array $data): array
    {
        try {
            $categories = $this->categoryProvider->getAllByProduct($product);
        } catch (NoSuchEntityException $e) {
            return $data;
        }

        $maxCategoriesCount = 5;
        $currentCategoriesCount = 1;
        foreach ($categories as $category) {
            if ((int)$category->getParentId() === 1) {
                continue;
            }

            $key = 'item_category' . ($currentCategoriesCount === 1 ? '' : $currentCategoriesCount);
            $data[$key] = $category->getName();

            $currentCategoriesCount++;
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
