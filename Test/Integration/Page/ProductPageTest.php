<?php declare(strict_types=1);

namespace Tagging\GTM\Test\Integration\Page;

use Magento\Catalog\Api\Data\CategoryInterface;
use Tagging\GTM\Test\Integration\FixtureTrait\CreateCategory;
use Tagging\GTM\Test\Integration\FixtureTrait\CreateProduct;
use Tagging\GTM\Test\Integration\PageTestCase;
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
     * @magentoConfigFixture current_store GTM/settings/enabled 1
     * @magentoConfigFixture current_store GTM/settings/serverside_gtm_url gtm.tryforwarder.com
     */
    public function testValidDataLayerWithOneCategory()
    {
        $this->assertEnabledFlagIsWorking();

        /** @var CategoryInterface $category */
        $category = $this->createCategory(3);
        $product = $this->createProducts(1, ['category_ids' => [$category->getId()]])[0];

        $this->dispatch('catalog/product/view/id/' . $product->getId());
        $this->assertRequestActionName('view');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString($product->getName(), $body);

        $block = $this->layout->getBlock('Tagging_GTM.data-layer');
        $this->assertNotEmpty($block);

        $this->assertDataLayerEquals('product', 'page_type');

        $event = $this->getEventFromDataLayerEvents('view_item_event', 'trytagging_view_item');
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
