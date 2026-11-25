<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Customer\CustomerData\SectionPool;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCustomer;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

class LoginTest extends PageTestCase
{
    use CreateCustomer;

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST123
     * @return void
     */
    public function testLogin()
    {
        $this->createCustomer();

        /** @var HttpRequest $request */
        $request = $this->getRequest();

        $request->setPostValue([
            'login' => [
                'username' => 'customer@example.com',
                'password' => 'password',
            ],
            'formKey' => $this->objectManager->get(FormKey::class)->getFormKey(),
        ]);
        $request->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('customer/account/loginPost');

        $customerSectionPool = $this->objectManager->get(SectionPool::class);
        $data = $customerSectionPool->getSectionsData(['customer']);

        $this->assertArrayHasKey('login_event', $data['customer']['gtm_events']);
        $this->assertEquals('login', $data['customer']['gtm_events']['login_event']['event']);
    }
}
