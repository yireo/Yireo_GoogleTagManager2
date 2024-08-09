<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\CustomerData\SectionPool;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCustomer;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

class AddToWishlistTest extends PageTestCase
{
    use CreateCustomer;

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation disabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testAddToWishlist()
    {
        $this->createCustomer();
        $this->loginCustomer();

        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $product->setData('is_saleable', 1);
        $productRepository->save($product);

        $wishlist = $this->getWishlist();
        $this->assertEquals(0, $wishlist->getItemsCount());

        /** @var HttpRequest $request */
        $request = $this->getRequest();

        $request->setPostValue([
            'wishlist_id' => $wishlist->getId(),
            'product' => $product->getId(),
            'formKey' => $this->objectManager->get(FormKey::class)->getFormKey(),
        ]);

        $request->setParam('isAjax', 1);
        $request->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('wishlist/index/add');

        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $wishlist->getItemsCount(), 'Wishlist items do not count to 1');

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
