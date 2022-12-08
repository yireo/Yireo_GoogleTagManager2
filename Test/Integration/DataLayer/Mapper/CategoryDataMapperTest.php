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
        $category = $this->createCategory(3);
        $categoryDataMapper = ObjectManager::getInstance()->get(CategoryDataMapper::class);
        $categoryData = $categoryDataMapper->mapByCategory($category);
        $this->assertEquals('Category ' . $category->getId(), $categoryData['category_name']);
        $this->assertEquals($category->getId(), $categoryData['category_id']);
    }
}
