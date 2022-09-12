<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
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
     */
    public function testValidDataLayerWithOneCategory()
    {
        $this->assertEnabledFlagIsWorking();

        /** @var CategoryInterface $category */
        $category = $this->createCategories()[0];
        $this->createProducts(3, ['category_ids' => [$category->getId()]]);

        $this->dispatch('catalog/category/view/id/' . $category->getId());
        $this->assertRequestActionName('view');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString($category->getName(), $body);

        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotEmpty($block);

        $this->assertDataLayerEquals($category->getName(), 'categoryName');
        $this->assertDataLayerEquals($category->getId(), 'categoryId');
        $this->assertDataLayerEquals(0, 'categorySize');
        $this->assertDataLayerEquals('catalog/category/view', 'pageType');

        $this->assertHandleInLayout('yireo_googletagmanager2_enhanced_ecommerce_catalog_category_view');
        $data = $this->getDataFromDataLayer();
        $this->assertArrayHasKey('ecommerce', $data);
        $this->assertArrayHasKey('impressions', $data['ecommerce']);

        $products = $this->getProductsByCategory($category);
        if (!count($products) > 0) {
            $this->markTestIncomplete('Category fixture ' . $category->getId() . ' does not have any products');
        }

        $products = $this->getProductsByCurrentCategory();
        if (!count($products) > 0) {
            $this->markTestIncomplete('Current category ' . $category->getId() . ' does not have any products');
        }

        $this->assertNotEmpty($data['ecommerce']['impressions']);
        foreach ($data['ecommerce']['impressions'] as $productData) {
            $this->assertNotEmpty($productData['name']);
            $this->assertNotEmpty($productData['id']);
            $this->assertNotEmpty($productData['sku']);
            $this->assertNotEmpty($productData['price']);
            $this->assertNotEmpty($productData['category']);
            $this->assertNotEmpty($productData['position']);
            // @todo: Test for brand, variant, etc?
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
