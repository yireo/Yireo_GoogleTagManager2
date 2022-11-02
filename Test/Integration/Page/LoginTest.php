<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\CustomerData\SectionPool;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

class LoginTest extends PageTestCase
{
    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @return void
     */
    public function testLogin()
    {
        $this->createCustomerFixture();

        $this->getRequest()->setPostValue([
            'login' => [
                'username' => 'customer@example.com',
                'password' => 'password',
            ],
            'formKey' => $this->objectManager->get(FormKey::class)->getFormKey(),
        ]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('customer/account/loginPost');

        $customerSectionPool = $this->objectManager->get(SectionPool::class);
        $data = $customerSectionPool->getSectionsData(['customer']);

        $this->assertArrayHasKey('login_event', $data['customer']['gtm_once']);
        $this->assertEquals('login', $data['customer']['gtm_once']['login_event']['event']);
    }

    private function createCustomerFixture()
    {
        $customer = $this->objectManager->create(Customer::class);
        $customer->setWebsiteId(1)
            ->setId(1)
            ->setEmail('customer@example.com')
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setPrefix('Mr.')
            ->setFirstname('John')
            ->setMiddlename('A')
            ->setLastname('Smith')
            ->setSuffix('Esq.')
            ->setDefaultBilling(1)
            ->setDefaultShipping(1)
            ->setTaxvat('12')
            ->setGender(0);

        $customer->isObjectNew(true);
        $customer->save();

        $customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $customerRegistry->remove($customer->getId());
    }
}
