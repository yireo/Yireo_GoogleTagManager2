<?php declare(strict_types=1);

// phpcs:ignoreFile -- Too many issues, lol

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCategory;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\GoogleTagManager2\Util\GetCurrentCategoryProducts;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertHandleInLayout;

/**
 * @magentoAppArea frontend
 */
class CategoryPageTest extends PageTestCase
{
    use CreateCategory;
    use CreateProduct;
    use AssertHandleInLayout;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoConfigFixture current_store googletagmanager2/settings/category_products 3
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoCache all disabled
     */
    public function testValidDataLayerWithOneCategory()
    {
        $this->assertEnabledFlagIsWorking();


        /** @var CategoryInterface $category */
        $category = $this->createCategory(3);
        $this->createProducts(3, ['category_ids' => [$category->getId()]]);
        $products = $category->getProductCollection();
        $this->assertTrue($products->count() >= 3, 'Product count is '.$products->count());

        $this->dispatch('catalog/category/view/id/' . $category->getId());
        $this->assertRequestActionName('view');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString($category->getName(), $body);
        $this->assertStringContainsString('"view_item_list"', $body);

        $productListBlock = $this->layout->getBlock('category.products.list');
        $productListBlock->setCollection($products);
        $this->assertInstanceOf(ListProduct::class, $productListBlock);
        $this->assertTrue($productListBlock->getLoadedProductCollection()->count() > 0);

        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotEmpty($block);

        $this->assertDataLayerEquals($category->getName(), 'category_name');
        $this->assertDataLayerEquals($category->getId(), 'category_id');
        $this->assertDataLayerEquals(count($productListBlock->getLoadedProductCollection()), 'category_size');
        $this->assertDataLayerEquals('category', 'page_type');

        $event = $this->getEventFromDataLayerEvents('view_item_list_event', 'view_item_list');
        $this->assertArrayHasKey('ecommerce', $event);
        $this->assertArrayHasKey('items', $event['ecommerce']);

        $products = $this->getProductsByCategory($category);
        if (!count($products) > 0) {
            $this->markTestIncomplete('Category fixture with ID "' . $category->getId() . '" does not have any products');
        }

        $products = $this->getProductsByCurrentCategory();
        if (!count($products) > 0) {
            $this->markTestIncomplete('Did not detect any products on category page with ID "' . $category->getId() . '"');
        }

        $this->assertNotEmpty($event['ecommerce']['items'], var_export($event, true));
        foreach ($event['ecommerce']['items'] as $productData) {
            $this->assertNotEmpty($productData['item_id']);
            $this->assertNotEmpty($productData['item_sku']);
            $this->assertNotEmpty($productData['item_list_name']);
        }
    }

    private function getProductsByCategory(CategoryInterface $category): array
    {
        return $category->getProductCollection()->toArray();
    }

    private function getProductsByCurrentCategory(): array
    {
        $getCurrentCategoryProducts = $this->objectManager->get(GetCurrentCategoryProducts::class);
        return $getCurrentCategoryProducts->getProducts();
    }
}
