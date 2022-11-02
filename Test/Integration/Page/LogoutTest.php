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

class LogoutTest extends PageTestCase
{
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @return void
     */
    public function testLogout()
    {
        $this->doLoginCustomer();

        $this->dispatch('customer/account/logout');

        $customerSectionPool = $this->objectManager->get(SectionPool::class);
        $customerSessionData = $customerSectionPool->getSectionsData(['customer']);

        $this->assertEquals('logout', $customerSessionData['customer']['gtm_once']['event']);
    }
}
