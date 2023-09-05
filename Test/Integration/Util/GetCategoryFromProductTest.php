<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Util;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCategory;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetProduct;
use Yireo\GoogleTagManager2\Util\GetCategoryFromProduct;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertNonEmptyValueInArray;

class GetCategoryFromProductTest extends TestCase
{
    use AssertNonEmptyValueInArray;
    use CreateProduct;
    use CreateCategory;
    use GetProduct;
    
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testGetAllWithProductInMultipleCategories()
    {
        $this->createCategory(3, 2, ['name' => 'Category 1', 'path' => '1/2/3']);
        $this->createCategory(4, 3, ['name' => 'Category 2', 'path' => '1/2/3/4']);
        $this->createCategory(5, 4, ['name' => 'Category 3', 'path' => '1/2/3/4/5']);
        
        $this->createProduct(
            1,
            [
                'name' => 'Product 1',
                'sku' => 'product1',
                'category_ids' => [3, 4, 5]
            ]
        );
        
        $product = $this->getProduct(1);
        $this->assertContains('3', $product->getCategoryIds());
        $this->assertContains('4', $product->getCategoryIds());
        $this->assertContains('5', $product->getCategoryIds());
        
        $getCategoryFromProduct = ObjectManager::getInstance()->get(GetCategoryFromProduct::class);
        $categories = $getCategoryFromProduct->getAll($product);
        $this->assertTrue(count($categories) === 3, 'Actual count ' . count($categories));
    }
    
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testGetAllWithProductInMultipleCategoriesAndOneDisabled()
    {
        $this->createCategory(3, 2, ['name' => 'Category A', 'path' => '1/2/3']);
        $this->createCategory(4, 3, ['name' => 'Category B', 'path' => '1/2/3/4']);
        $this->createCategory(5, 4, ['name' => 'Category C', 'path' => '1/2/3/4/5', 'is_active' => 0]);
        
        $this->createProduct(
            1,
            [
                'name' => 'Product 1',
                'sku' => 'product1',
                'category_ids' => [3, 4, 5]
            ]
        );
        
        $product = $this->getProduct(1);
        $this->assertContains('3', $product->getCategoryIds());
        $this->assertContains('4', $product->getCategoryIds());
        $this->assertContains('5', $product->getCategoryIds());
        
        $getCategoryFromProduct = ObjectManager::getInstance()->get(GetCategoryFromProduct::class);
        $categories = $getCategoryFromProduct->getAll($product);
        $this->assertEquals(2, count($categories), 'Actual count ' . count($categories));
    }
}
