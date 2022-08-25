<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Mapper;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CategoryDataMapper;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCategory;

class CategoryDataMapperTest extends TestCase
{
    use CreateCategory;

    public function testMapByCategory()
    {
        $category = $this->createCategories()[0];
        $categoryDataMapper = ObjectManager::getInstance()->get(CategoryDataMapper::class);
        $categoryData = $categoryDataMapper->mapByCategory($category);
        $this->assertEquals('Category 3', $categoryData['name']);
        $this->assertEquals('3', $categoryData['id']);
    }
}
