<?php

declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Tagging\GTM\Api\Data\ProductTagInterface;
use Tagging\GTM\Api\Data\TagInterface;
use Tagging\GTM\Config\Config;
use Tagging\GTM\Util\Attribute\GetAttributeValue;
use Tagging\GTM\Util\PriceFormatter;
use Tagging\GTM\Util\CategoryProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductDataMapper
{
    private Config $config;
    private GetAttributeValue $getAttributeValue;
    private CategoryProvider $categoryProvider;
    private PriceFormatter $priceFormatter;
    private Configurable $configurableType;
    private ProductRepositoryInterface $productRepository;

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
        Configurable $configurableType,
        ProductRepositoryInterface $productRepository,
        array $dataLayerMapping = []
    ) {
        $this->config = $config;
        $this->getAttributeValue = $getAttributeValue;
        $this->categoryProvider = $categoryProvider;
        $this->priceFormatter = $priceFormatter;
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
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
        $productFields = $this->getProductFields();
        foreach ($productFields as $productAttributeCode) {
            $dataLayerKey = $prefix . $productAttributeCode;
            $attributeValue = $this->getAttributeValue->getProductAttributeValue($product, $productAttributeCode);
            if ($attributeValue === null) {
                continue;
            }

            $productData[$dataLayerKey] = $attributeValue;
        }

        $brand = $product->getAttributeText('manufacturer') === false ? null : $product->getAttributeText('manufacturer');

        $productData['item_id'] = $product->getSku();
        $productData['item_sku'] = $product->getSku();
        $productData['magento_sku'] = $product->getSku();
        $productData['magento_id'] = $product->getId();
        $productData['item_brand'] = $brand;

        $parentIds = $this->configurableType->getParentIdsByChild($product->getId());

        if (!empty($parentIds)) {
            $parentProduct = $this->productRepository->getById($parentIds[0]);
            $productData['item_id'] = $parentProduct->getSku();
            $productData['item_variant'] = $product->getSku();
        }

        try {
            $category = $this->categoryProvider->getFirstByProduct($product);
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
     * @return string[]
     */
    private function getProductFields(): array
    {
        return array_filter(['id', 'name', 'brand']);
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
