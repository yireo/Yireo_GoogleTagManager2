<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Util;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;

class ProductDataMapperTest extends TestCase
{
    public function testMapByProduct()
    {
        $product = $this->createProduct(
            1,
            'Product 1',
            'product1',
            1.42,
        );

        $productDataMapper = ObjectManager::getInstance()->get(ProductDataMapper::class);
        $productData = $productDataMapper->mapByProduct($product);
        $this->assertSame(1, $productData['item_id'], var_export($productData, true));
        $this->assertSame('Product 1', $productData['item_name']);
        $this->assertSame('product1', $productData['item_sku']);
        $this->assertSame(1.42, $productData['price']);
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $sku
     * @param float $price
     * @return ProductInterface
     */
    private function createProduct(int $id, string $name, string $sku, float $price): ProductInterface
    {
        $productFactory = ObjectManager::getInstance()->get(ProductInterfaceFactory::class);
        $product = $productFactory->create();
        $product->setId($id);
        $product->setName($name);
        $product->setSku($sku);
        $product->setPrice($price);
        return $product;
    }
}
