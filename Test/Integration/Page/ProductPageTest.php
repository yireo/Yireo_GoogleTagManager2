<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\Data\CategoryInterface;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCategory;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertHandleInLayout;

/**
 * @magentoAppArea frontend
 */
class ProductPageTest extends PageTestCase
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
        $product = $this->createProducts(1, ['category_ids' => [$category->getId()]])[0];

        $this->dispatch('catalog/product/view/id/' . $product->getId());
        $this->assertRequestActionName('view');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString($product->getName(), $body);

        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotEmpty($block);

        $this->assertDataLayerEquals('product', 'page_type');

        $events = $this->getEventsFromDataLayer();
        $this->assertArrayHasKey('view_item_event', $events);
        $event = $events['view_item_event'];
        $this->assertArrayHasKey('view_item', $event['event']);
        $this->assertArrayHasKey('ecommerce', $event);
        $this->assertNotEmpty($event['ecommerce']['items']);

        $productData = array_shift($event['ecommerce']['items']);
        $this->assertNotEmpty($productData['item_name']);
        $this->assertNotEmpty($productData['item_id']);
        $this->assertNotEmpty($productData['price']);
        $this->assertNotEmpty($productData['item_list_id']);
        $this->assertNotEmpty($productData['item_list_name']);
    }
}
