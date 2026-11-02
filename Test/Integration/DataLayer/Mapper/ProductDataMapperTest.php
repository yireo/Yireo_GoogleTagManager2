<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Mapper;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetCategory;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetProduct;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GtmCategoryTree;
use Yireo\GoogleTagManager2\Util\CategoryProvider;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class ProductDataMapperTest extends TestCase
{
    use GetProduct;
    use GetCategory;
    use GtmCategoryTree;

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

    /**
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
     */
    public function testMapByProductWithMultipleCategories()
    {
        $product = $this->getProductBySku('gtm-multi-category');
        $productData = $this->createProductDataMapper()->mapByProduct($product);

        $parent = $this->getCategoryByName('GTM Parent');
        $this->assertEquals($parent->getId(), $productData['item_list_id'], json_encode($productData));
        $this->assertSame('GTM Parent', $productData['item_list_name'], json_encode($productData));
        $this->assertValidGtmItemCategories($productData);
        $this->assertArrayNotHasKey('item_category6', $productData);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/product_list_value_on_category current_category
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
     */
    public function testMapByProductWithCurrentCategoryAsListValue()
    {
        $product = $this->getProductBySku('gtm-multi-category');
        $child = $this->getCategoryByName('GTM Child');
        $product->setCategory($child);

        $productData = $this->createProductDataMapper()->mapByProduct($product);
        $this->assertEquals($child->getId(), $productData['item_list_id'], json_encode($productData));
        $this->assertSame('GTM Child', $productData['item_list_name'], json_encode($productData));
        $this->assertValidGtmItemCategories($productData);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/product_list_value_on_category product_first_category
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
     */
    public function testMapByProductIgnoresCurrentCategoryWhenFirstCategoryIsConfigured()
    {
        $product = $this->getProductBySku('gtm-multi-category');
        $product->setCategory($this->getCategoryByName('GTM Child'));

        $productData = $this->createProductDataMapper()->mapByProduct($product);
        $this->assertSame('GTM Parent', $productData['item_list_name'], json_encode($productData));
    }

    /**
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
     */
    public function testMapByProductWithoutUsableCategories()
    {
        $product = $this->getProductBySku('gtm-no-usable-category');
        $productData = $this->createProductDataMapper()->mapByProduct($product);

        $this->assertSame('gtm-no-usable-category', $productData['item_id']);
        $this->assertArrayNotHasKey('item_list_id', $productData, json_encode($productData));
        $this->assertArrayNotHasKey('item_list_name', $productData, json_encode($productData));
        $this->assertSame([], $this->getItemCategories($productData), json_encode($productData));
    }

    /**
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
     * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree_store_values.php
     */
    public function testMapByProductUsesStoreViewSpecificCategoryNames()
    {
        $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $currentStoreId = (int)$storeManager->getStore()->getId();
        $storeManager->setCurrentStore('fixture_second_store');

        try {
            $product = $this->getProductBySku('gtm-multi-category');
            $productData = $this->createProductDataMapper()->mapByProduct($product);
        } finally {
            $storeManager->setCurrentStore($currentStoreId);
        }

        $this->assertSame('GTM Parent (Second Store)', $productData['item_list_name'], json_encode($productData));
        $itemCategories = $this->getItemCategories($productData);
        $this->assertCount(5, $itemCategories, json_encode($productData));
        $this->assertNotContains('GTM Parent', $itemCategories);
        $this->assertNotContains('GTM Store Disabled', $itemCategories);
    }

    private function createProductDataMapper(): ProductDataMapper
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create(ProductDataMapper::class, [
            'categoryProvider' => $objectManager->create(CategoryProvider::class),
        ]);
    }
}
