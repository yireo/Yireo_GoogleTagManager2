<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Util;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CategoryDataMapper;
use Yireo\GoogleTagManager2\Exception\NotUsingSetProductSkusException;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetCategory;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetProduct;
use Yireo\GoogleTagManager2\Util\CategoryProvider;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
 */
class CategoryProviderTest extends TestCase
{
    use GetCategory;
    use GetProduct;

    private const ACTIVE_CATEGORY_NAMES = [
        'GTM Parent',
        'GTM Child',
        'GTM Grandchild',
        'GTM Extra 1',
        'GTM Extra 2',
        'GTM Extra 3',
        'GTM Store Disabled',
    ];

    public function testGetAllByProductReturnsOnlyActiveCategoriesWithinStoreRoot()
    {
        $product = $this->getProductBySku('gtm-multi-category');
        $categories = $this->createCategoryProvider()->getAllByProduct($product);

        $this->assertEqualsCanonicalizing(self::ACTIVE_CATEGORY_NAMES, $this->getNames($categories));
        foreach ($categories as $categoryId => $category) {
            $this->assertInstanceOf(CategoryInterface::class, $category);
            $this->assertSame((int)$category->getId(), $categoryId);
        }
    }

    public function testGetAllByProductDeliversDataUsedByTheDataLayer()
    {
        $product = $this->getProductBySku('gtm-multi-category');
        $categories = $this->createCategoryProvider()->getAllByProduct($product);

        $rootCategoryId = $this->getRootCategoryId();
        foreach ($categories as $category) {
            $this->assertNotEmpty($category->getName());
            $this->assertTrue((bool)$category->getIsActive());
            $this->assertGreaterThan(1, (int)$category->getParentId());
            $this->assertStringStartsWith('1/' . $rootCategoryId . '/', (string)$category->getPath());
        }

        $grandchild = $this->getCategoryByName('GTM Grandchild');
        $child = $this->getCategoryByName('GTM Child');
        $this->assertSame((int)$child->getId(), (int)$categories[(int)$grandchild->getId()]->getParentId());
    }

    public function testRootCategoryIsFilteredOut()
    {
        $categoryProvider = $this->createCategoryProvider();
        $categoryProvider->addCategoryIds([$this->getRootCategoryId()]);

        $this->expectException(NotUsingSetProductSkusException::class);
        $categoryProvider->getLoadedCategories();
    }

    public function testGetLoadedCategoriesWithoutCategoryIdsThrowsException()
    {
        $this->expectException(NotUsingSetProductSkusException::class);
        $this->createCategoryProvider()->getLoadedCategories();
    }

    public function testGetById()
    {
        $parent = $this->getCategoryByName('GTM Parent');
        $inactive = $this->getCategoryByName('GTM Inactive');

        $categoryProvider = $this->createCategoryProvider();
        $categoryProvider->addCategoryIds([(int)$parent->getId(), (int)$inactive->getId()]);

        $category = $categoryProvider->getById((int)$parent->getId());
        $this->assertSame((int)$parent->getId(), (int)$category->getId());
        $this->assertSame('GTM Parent', $category->getName());

        $this->expectException(NotUsingSetProductSkusException::class);
        $categoryProvider->getById((int)$inactive->getId());
    }

    public function testCategoriesAreLoadedIncrementallyAndOnlyOnce()
    {
        $parent = $this->getCategoryByName('GTM Parent');
        $child = $this->getCategoryByName('GTM Child');
        $extra1 = $this->getCategoryByName('GTM Extra 1');

        $categoryProvider = $this->createCategoryProvider();
        $categoryProvider->addCategoryIds([(int)$parent->getId(), (string)$child->getId()]);
        $firstRun = $categoryProvider->getLoadedCategories();
        $this->assertCount(2, $firstRun);

        $categoryProvider->addCategoryIds([(int)$child->getId(), (int)$extra1->getId()]);
        $secondRun = $categoryProvider->getLoadedCategories();
        $this->assertCount(3, $secondRun);
        $this->assertSame($firstRun[(int)$parent->getId()], $secondRun[(int)$parent->getId()]);
        $this->assertSame($firstRun[(int)$child->getId()], $secondRun[(int)$child->getId()]);
    }

    public function testGetFirstByProductSkipsInactiveAndOutOfRootCategories()
    {
        $product = $this->getProductBySku('gtm-multi-category');
        $category = $this->createCategoryProvider()->getFirstByProduct($product);

        $this->assertSame('GTM Parent', $category->getName());
        $this->assertSame((int)$this->getCategoryByName('GTM Parent')->getId(), (int)$category->getId());
    }

    public function testGetFirstByProductWithoutUsableCategoryThrowsException()
    {
        $product = $this->getProductBySku('gtm-no-usable-category');

        $this->expectException(NoSuchEntityException::class);
        $this->createCategoryProvider()->getFirstByProduct($product);
    }

    public function testGetAllByProductWithoutUsableCategoryReturnsNothing()
    {
        $product = $this->getProductBySku('gtm-no-usable-category');
        $this->assertSame([], $this->createCategoryProvider()->getAllByProduct($product));
    }

    /**
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree_store_values.php
     */
    public function testStoreViewSpecificValuesAreUsed()
    {
        $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $currentStoreId = (int)$storeManager->getStore()->getId();
        $storeManager->setCurrentStore('fixture_second_store');

        try {
            $product = $this->getProductBySku('gtm-multi-category');
            $categoryProvider = $this->createCategoryProvider();
            $names = $this->getNames($categoryProvider->getAllByProduct($product));
            $firstCategory = $categoryProvider->getFirstByProduct($product);
        } finally {
            $storeManager->setCurrentStore($currentStoreId);
        }

        $this->assertContains('GTM Parent (Second Store)', $names);
        $this->assertNotContains('GTM Parent', $names);
        $this->assertNotContains('GTM Store Disabled', $names);
        $this->assertContains('GTM Child', $names);
        $this->assertSame('GTM Parent (Second Store)', $firstCategory->getName());
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/category_eav_attributes id,name,meta_title
     */
    public function testConfiguredCategoryEavAttributesAreAvailable()
    {
        $product = $this->getProductBySku('gtm-multi-category');
        $parentId = (int)$this->getCategoryByName('GTM Parent')->getId();
        $categories = $this->createCategoryProvider()->getAllByProduct($product);
        $this->assertArrayHasKey($parentId, $categories);

        $categoryDataMapper = ObjectManager::getInstance()->get(CategoryDataMapper::class);
        $categoryData = $categoryDataMapper->mapByCategory($categories[$parentId]);

        $this->assertSame($parentId, (int)$categoryData['category_id'], json_encode($categoryData));
        $this->assertSame('GTM Parent', $categoryData['category_name'], json_encode($categoryData));
        $this->assertArrayHasKey(
            'category_meta_title',
            $categoryData,
            'Attribute "meta_title" configured in "category_eav_attributes" is not loaded: ' . json_encode($categoryData)
        );
        $this->assertSame('GTM Parent Meta Title', $categoryData['category_meta_title']);
    }

    private function createCategoryProvider(): CategoryProvider
    {
        return ObjectManager::getInstance()->create(CategoryProvider::class);
    }

    private function getRootCategoryId(): int
    {
        return (int)ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore()->getRootCategoryId();
    }

    /**
     * @param CategoryInterface[] $categories
     * @return string[]
     */
    private function getNames(array $categories): array
    {
        return array_values(array_map(static fn(CategoryInterface $category) => (string)$category->getName(), $categories));
    }
}
