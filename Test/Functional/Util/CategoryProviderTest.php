<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Functional\Util;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Exception\NotUsingSetProductSkusException;
use Yireo\GoogleTagManager2\Util\CategoryProvider;

class CategoryProviderTest extends TestCase
{
    public function testGetCategoriesWithException()
    {
        $this->expectException(NotUsingSetProductSkusException::class);
        $categoryProvider = ObjectManager::getInstance()->get(CategoryProvider::class);
        $categoryProvider->getLoadedCategories();
    }

    public function testGetById()
    {
        $categoryProvider = ObjectManager::getInstance()->get(CategoryProvider::class);
        $categoryProvider->addCategoryIds([11, 38]);
        $category = $categoryProvider->getById(11);
        $this->assertEquals(11, $category->getId());
    }

    public function testGetCategories()
    {
        $categoryProvider = ObjectManager::getInstance()->get(CategoryProvider::class);
        $categoryProvider->addCategoryIds([11, 38]);
        $categories = $categoryProvider->getLoadedCategories();
        $this->assertEquals(2, count($categories));

        $categoryProvider->addCategoryIds(['12', 14]);
        $categories = $categoryProvider->getLoadedCategories();
        $this->assertEquals(4, count($categories));

        $categoryProvider->addCategoryIds([12, 14]);
        $categories = $categoryProvider->getLoadedCategories();
        $this->assertEquals(4, count($categories));
    }

    public function testGetByProduct()
    {
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->getById(1);

        $categoryProvider = ObjectManager::getInstance()->get(CategoryProvider::class);
        $categoryProvider->addCategoryIds([11, 38]);
        $categories = $categoryProvider->getAllByProduct($product);
        $this->assertEquals(2, count($categories));
    }
}
