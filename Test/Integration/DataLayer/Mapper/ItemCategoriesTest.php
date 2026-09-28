<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Mapper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToCart;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToWishlist;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CartItemDataMapper;
use Yireo\GoogleTagManager2\DataLayer\Mapper\OrderItemDataMapper;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetProduct;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GtmCategoryTree;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
 * @magentoConfigFixture current_store googletagmanager2/settings/id test
 * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
 */
class ItemCategoriesTest extends TestCase
{
    use GetProduct;
    use GtmCategoryTree;

    public function testAddToCartEventContainsCategories()
    {
        /** @var Product $product */
        $product = $this->getProductBySku('gtm-multi-category');
        $data = ObjectManager::getInstance()->get(AddToCart::class)->setProduct($product)->get();

        $this->assertCount(1, $data['ecommerce']['items']);
        $item = $data['ecommerce']['items'][0];
        $this->assertSame('GTM Parent', $item['item_list_name'], json_encode($item));
        $this->assertValidGtmItemCategories($item);
    }

    public function testAddToWishlistEventContainsCategories()
    {
        /** @var Product $product */
        $product = $this->getProductBySku('gtm-multi-category');
        $data = ObjectManager::getInstance()->get(AddToWishlist::class)->setProduct($product)->get();

        $item = $data['ecommerce']['items'][0];
        $this->assertSame('GTM Parent', $item['item_list_name'], json_encode($item));
        $this->assertValidGtmItemCategories($item);
    }

    public function testCartItemContainsCategories()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var Quote $quote */
        $quote = $objectManager->get(QuoteFactory::class)->create();
        $quote->setStoreId(1);
        $quote->addProduct($this->getProductBySku('gtm-multi-category'), 1);
        $quote->collectTotals();

        $cartItem = $quote->getAllVisibleItems()[0];
        $item = $objectManager->get(CartItemDataMapper::class)->mapByCartItem($cartItem);

        $this->assertSame('gtm-multi-category', $item['item_sku']);
        $this->assertSame('GTM Parent', $item['item_list_name'], json_encode($item));
        $this->assertValidGtmItemCategories($item);
    }

    public function testOrderItemContainsCategories()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var OrderItem $orderItem */
        $orderItem = $objectManager->get(OrderItemInterfaceFactory::class)->create();
        $orderItem->setSku('gtm-multi-category');
        $orderItem->setName('GTM Multi Category Product');
        $orderItem->setProductType('simple');
        $orderItem->setQtyOrdered(1);
        $orderItem->setPrice(10);
        $orderItem->setPriceInclTax(10);
        $orderItem->setRowTotal(10);
        $orderItem->setRowTotalInclTax(10);
        $orderItem->setStoreId(1);

        $item = $objectManager->get(OrderItemDataMapper::class)->mapByOrderItem($orderItem);

        $this->assertSame('gtm-multi-category', $item['item_id']);
        $this->assertSame('GTM Parent', $item['item_list_name'], json_encode($item));
        $this->assertValidGtmItemCategories($item);
    }
}
