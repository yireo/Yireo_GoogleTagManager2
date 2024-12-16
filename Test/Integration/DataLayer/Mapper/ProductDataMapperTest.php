<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Mapper;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetProduct;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class ProductDataMapperTest extends TestCase
{
    use GetProduct;

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     */
    public function testMapByProduct()
    {
        $product = $this->getProductBySku('simple1002');
        $productDataMapper = ObjectManager::getInstance()->get(ProductDataMapper::class);
        $productData = $productDataMapper->mapByProduct($product);
        $this->assertArrayHasKey('item_name', $productData);
        $this->assertStringContainsString('Simple Product', $productData['item_name']);
    }
}
