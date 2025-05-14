<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Event;

use Magento\Checkout\Model\Cart as CartModel;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
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
        $this->assertEquals(1, (int)$data['ecommerce']['items'][0]['quantity']);
    }
}
