<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Event;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\ViewCart;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;

/**
 * @magentoAppArea frontend
 */
class ViewCartTest extends TestCase
{
    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_products.php
     */
    public function testValidViewCartEvent()
    {
        $om = ObjectManager::getInstance();

        $quoteRepository = ObjectManager::getInstance()->get(CartRepositoryInterface::class);
        $searchCriteriaBuilder = ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('reserved_order_id', 'test_quote_with_simple_products');
        $searchCriteriaBuilder->setPageSize(1);
        $searchResults = $quoteRepository->getList($searchCriteriaBuilder->create());
        $quote = $searchResults->getItems()[0];
        $cartId = $quote->getId();

        $checkoutSession = ObjectManager::getInstance()->get(Session::class);
        $checkoutSession->setQuoteId($cartId);
        $checkoutSession->getQuote()->collectTotals();
        $quoteRepository->save($checkoutSession->getQuote());

        $cartRepository = ObjectManager::getInstance()->get(CartRepositoryInterface::class);
        $cart = $cartRepository->get($cartId);
        $this->assertNotEmpty($cart->getItems());
        $this->assertCount(2, $cart->getItems());

        $cartItems = $om->create(CartItems::class, ['cart' => $cart]);
        $viewCartEvent = $om->create(ViewCart::class, ['cartItems' => $cartItems]);

        $data = $viewCartEvent->get();
        $this->assertArrayHasKey('meta', $data);
        $this->assertTrue($data['meta']['cacheable']);
        $this->assertEquals('view_cart', $data['event']);
        $this->assertEquals('USD', $data['ecommerce']['currency']);
        $this->assertNotEquals(0.0, $data['ecommerce']['value']);
        $this->assertNotEmpty($data['ecommerce']['items'], 'No ecommerce items found');
        $this->assertArrayHasKey('quantity', $data['ecommerce']['items'][0], json_encode($data));
        $this->assertEquals(1, (int)$data['ecommerce']['items'][0]['quantity'], json_encode($data));
    }

    #[DataProvider('getMetaConfigProvider')]
    public function testMetaFollowsConfiguration(
        string $occurances,
        int $expandOnly,
        array $expectedAllowedPages,
        array $expectedAllowedEvents
    ) {
        $om = ObjectManager::getInstance();
        $scopeConfig = $om->get(MutableScopeConfigInterface::class);
        $values = [
            'googletagmanager2/settings/view_cart_occurances' => $occurances,
            'googletagmanager2/settings/view_cart_on_mini_cart_expand_only' => $expandOnly,
        ];
        $originalValues = [];
        foreach ($values as $path => $value) {
            $originalValues[$path] = $scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, 'default');
            $scopeConfig->setValue($path, $value, ScopeInterface::SCOPE_STORE, 'default');
        }

        $cartItems = $this->createMock(CartItems::class);
        $cartItems->method('get')->willReturn([['item_id' => 'sku-1', 'quantity' => 1]]);
        $viewCartEvent = $om->create(ViewCart::class, ['cartItems' => $cartItems]);

        try {
            $meta = $viewCartEvent->get()['meta'];
        } finally {
            foreach ($originalValues as $path => $value) {
                $scopeConfig->setValue($path, $value, ScopeInterface::SCOPE_STORE, 'default');
            }
        }

        $this->assertSame($expectedAllowedPages, $meta['allowed_pages']);
        $this->assertSame($expectedAllowedEvents, $meta['allowed_events']);
    }

    public static function getMetaConfigProvider(): array
    {
        return [
            'cart page only' => ['cart_page', 0, ['checkout/cart/'], []],
            'cart page only, expand only is ignored' => ['cart_page', 1, ['checkout/cart/'], []],
            'everywhere' => ['everywhere', 0, [], []],
            'everywhere, only on minicart expand' => ['everywhere', 1, [], ['minicart_collapse']],
        ];
    }
}
