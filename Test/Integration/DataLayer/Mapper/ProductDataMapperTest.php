<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Mapper;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;

class ProductDataMapperTest extends TestCase
{
    use CreateProduct;

    public function testMapByProduct()
    {
        $product = $this->createProducts()[0];
        $productDataMapper = ObjectManager::getInstance()->get(ProductDataMapper::class);
        $productData = $productDataMapper->mapByProduct($product);
        $this->assertEquals('Product 1', $productData['item_name']);
        $this->assertEquals('product1', $productData['item_id']);
    }
}
