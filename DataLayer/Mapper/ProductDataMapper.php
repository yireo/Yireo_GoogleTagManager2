<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Mapper;

use Magento\Catalog\Api\Data\ProductInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\ProductCategory;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\ProductPrice;

class ProductDataMapper
{
    private ProductCategory $productCategory;
    private ProductPrice $productPrice;

    /**
     * @param ProductCategory $productCategory
     * @param ProductPrice $productPrice
     */
    public function __construct(
        ProductCategory $productCategory,
        ProductPrice $productPrice
    ) {
        $this->productCategory = $productCategory;
        $this->productPrice = $productPrice;
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
            $productData[$dataLayerKey] = $product->getData($attributeName);
        }

        $this->productCategory->setProduct($product);
        $productData['category'] = $this->productCategory->addData();

        $this->productPrice->setProduct($product);
        $productData['price'] = $this->productPrice->addData();

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
