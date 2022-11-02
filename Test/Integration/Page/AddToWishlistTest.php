<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\CustomerData\SectionPool;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\Manager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Yireo\GoogleTagManager2\SessionDataProvider\CustomerSessionDataProvider;
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
        $customerId = 1;
        $customerSession = $this->objectManager->get(CustomerSession::class);
        $customerSession->loginById($customerId);
        $this->assertTrue($customerSession->isLoggedIn());

        $formKey = $this->objectManager->get(FormKey::class);
        $wishlistProvider = $this->objectManager->get(WishlistProviderInterface::class);
        $wishlist = $wishlistProvider->getWishlist();
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $this->assertEquals(0, $wishlist->getItemsCount());
        $this->assertEquals(1, $wishlist->getCustomerId());

        $productId = $product->getId();
        $data = [
            'product' => $productId,
            'formKey' => $formKey->getFormKey(),
        ];

        $this->getRequest()->setPostValue($data);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/add');

        $this->assertEquals(1, $wishlist->getItemsCount());

        $customerSectionPool = $this->objectManager->get(SectionPool::class);
        $customerSessionData = $customerSectionPool->getSectionsData(['customer']);

        $this->assertArrayHasKey('customer', $customerSessionData);
        $this->assertArrayHasKey('gtm_once', $customerSessionData['customer']);
        $this->assertEquals('event', $customerSessionData['customer']['gtm_once']);
        $this->assertEquals('ecommerce', $customerSessionData['customer']['gtm_once']);
    }
}
