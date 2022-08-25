<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertHandleInLayout;

/**
 * @magentoAppArea frontend
 */
class ProductPageTest extends PageTestCase
{
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

        $product = $this->createProducts()[0];

        $this->dispatch('catalog/product/view/id/' . $product->getId());
        $this->assertRequestActionName('view');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString($product->getName(), $body);

        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotEmpty($block);

        $this->assertDataLayerEquals($product->getName(), 'productName');
        $this->assertDataLayerEquals($product->getId(), 'productId');
        $this->assertDataLayerEquals('catalog/product/view', 'pageType');

        $this->assertHandleInLayout('yireo_googletagmanager2_enhanced_ecommerce_catalog_product_view');
        $data = $this->getDataFromDataLayer();
        $this->assertArrayHasKey('ecommerce', $data);
        $this->assertArrayHasKey('detail', $data['ecommerce']);
        $this->assertNotEmpty($data['ecommerce']['detail']);
        $this->assertNotEmpty($data['ecommerce']['detail'][0]);

        $productData = $data['ecommerce']['detail'][0];
        $this->assertNotEmpty($productData['name']);
        $this->assertNotEmpty($productData['id']);
        $this->assertNotEmpty($productData['price']);
        $this->assertNotEmpty($productData['category']);

        $actionFieldList = $data['ecommerce']['actionField']['list'];
        $this->assertNotEmpty($actionFieldList);
    }
}
