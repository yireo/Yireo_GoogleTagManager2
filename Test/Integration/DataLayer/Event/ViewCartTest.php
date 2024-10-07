<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Event;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\ViewCart;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;

/**
 * @magentoAppArea frontend
 */
class ViewCartTest extends TestCase
{
    use CreateProduct;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testValidViewCartEvent()
    {
        $om = ObjectManager::getInstance();
        $cart = $om->create(CartInterface::class);

        $product = $this->createProduct(1);
        $item = $cart->addProduct($product);
        $item->setQty(2);
        $cart->setItems([$item]);
        $cart->setSubtotal(42.0);
        $om->get(CartModel::class)->setQuote($cart);

        $this->assertNotEmpty($cart->getItems());
        $this->assertCount(1, $cart->getItems());

        $cartItems = $om->create(CartItems::class, ['cart' => $cart]);
        $viewCartEvent = $om->create(ViewCart::class, ['cartItems' => $cartItems]);

        $data = $viewCartEvent->get();
        $this->assertTrue($data['meta']['cacheable']);
        $this->assertEquals('view_cart', $data['event']);
        $this->assertEquals('USD', $data['ecommerce']['currency']);
        $this->assertNotEquals(0.0, $data['ecommerce']['value']);
        $this->assertNotEmpty($data['ecommerce']['items'], 'No ecommerce items found');
        $this->assertEquals(2, (int)$data['ecommerce']['items'][0]['quantity']);
    }
}
