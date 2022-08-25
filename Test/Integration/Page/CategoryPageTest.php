<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCategory;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertHandleInLayout;

/**
 * @magentoAppArea frontend
 */
class CategoryPageTest extends PageTestCase
{
    use CreateCategory;
    use AssertHandleInLayout;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     */
    public function testValidDataLayerWithOneCategory()
    {
        $this->assertEnabledFlagIsWorking();

        $category = $this->createCategories()[0];

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
        $this->assertNotEmpty($data['ecommerce']['impressions']);
    }
}
