<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Mapper;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CategoryDataMapper;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetCategory;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class CategoryDataMapperTest extends TestCase
{
    use GetCategory;

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     */
    public function testMapByCategory()
    {
        $category = $this->getCategoryByName('Category 999');
        $categoryDataMapper = ObjectManager::getInstance()->get(CategoryDataMapper::class);
        $categoryData = $categoryDataMapper->mapByCategory($category);
        $this->assertEquals('Category 999', $categoryData['category_name']);
        $this->assertEquals($category->getId(), $categoryData['category_id']);
    }
}
