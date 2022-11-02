<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\CustomerData\SectionPool;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

class AddToWishlistTest extends PageTestCase
{
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testAddToWishlist()
    {
        $this->doLoginCustomer();

        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $wishlist = $this->getWishlist();
        $this->assertEquals(0, $wishlist->getItemsCount());

        $this->getRequest()->setPostValue([
            'product' => $product->getId(),
            'formKey' => $this->objectManager->get(FormKey::class)->getFormKey(),
        ]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/add');

        $this->assertEquals(1, $wishlist->getItemsCount());

        $customerSectionPool = $this->objectManager->get(SectionPool::class);
        $customerSessionData = $customerSectionPool->getSectionsData(['customer']);

        $this->assertEquals('add_to_wishlist', $customerSessionData['customer']['gtm_once']['event']);
        $this->assertArrayHasKey('items', $customerSessionData['customer']['gtm_once']['ecommerce']);
        $this->assertEquals(1, count($customerSessionData['customer']['gtm_once']['ecommerce']));
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
