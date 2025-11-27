<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetProduct;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertHandleInLayout;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class ProductPageTest extends PageTestCase
{
    use GetProduct;
    use AssertHandleInLayout;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoConfigFixture current_store catalog/seo/generate_category_product_rewrites 0
     * @magentoConfigFixture static_content_on_demand_in_production 1
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     */
    public function testValidDataLayerWithOneCategory()
    {
        $this->assertEnabledFlagIsWorking();

        $product = $this->getProductBySku('simple1002');

        $this->dispatch('catalog/product/view/id/' . $product->getId());
        $this->assertRequestActionName('view');

        /** @var Http $response */
        $response = $this->getResponse();
        $body = $response->getBody();
        $this->assertStringContainsString($product->getName(), $body);

        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotEmpty($block);

        $this->assertDataLayerEquals('product', 'page_type');

        $event = $this->getEventFromDataLayerEvents('view_item_event', 'view_item');
        $this->assertArrayHasKey('ecommerce', $event);
        $this->assertNotEmpty($event['ecommerce']['items']);

        $productData = array_shift($event['ecommerce']['items']);
        $this->assertNonEmptyValueInArray('item_name', $productData);
        $this->assertNonEmptyValueInArray('item_id', $productData);
        $this->assertNonEmptyValueInArray('price', $productData);
        $this->assertNonEmptyValueInArray('item_list_id', $productData);
        $this->assertNonEmptyValueInArray('item_list_name', $productData);
    }
}
