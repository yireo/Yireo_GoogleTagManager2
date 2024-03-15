<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\CustomerData\SectionPool;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCustomer;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

class AddToWishlistTest extends PageTestCase
{
    use CreateCustomer;

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testAddToWishlist()
    {
        $this->createCustomer();
        $this->loginCustomer();

        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $wishlist = $this->getWishlist();
        $this->assertEquals(0, $wishlist->getItemsCount());

        /** @var HttpRequest $request */
        $request = $this->getRequest();

        $request->setPostValue([
            'product' => $product->getId(),
            'formKey' => $this->objectManager->get(FormKey::class)->getFormKey(),
        ]);

        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/add');

        $this->assertEquals(1, $wishlist->getItemsCount());

        $customerSectionPool = $this->objectManager->get(SectionPool::class);
        $data = $customerSectionPool->getSectionsData(['customer']);

        $this->assertArrayHasKey('add_to_wishlist_event', $data['customer']['gtm_events']);
        $this->assertEquals('add_to_wishlist', $data['customer']['gtm_events']['add_to_wishlist_event']['event']);
        $this->assertArrayHasKey('items', $data['customer']['gtm_events']['add_to_wishlist_event']['ecommerce']);
        $this->assertEquals(1, count($data['customer']['gtm_events']['add_to_wishlist_event']['ecommerce']['items']));
    }

    /**
     * @return Wishlist
     * @throws LocalizedException
     */
    private function getWishlist(): Wishlist
    {
        $wishlistProvider = $this->objectManager->get(WishlistProviderInterface::class);
        $wishlist = $wishlistProvider->getWishlist();
        $this->assertEquals(1, $wishlist->getCustomerId());
        return $wishlist;
    }
}
