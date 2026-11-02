<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\CustomerData\Customer;
use Magento\Customer\CustomerData\SectionPool;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Serialize\SerializerInterface;
use Yireo\GoogleTagManager2\Plugin\AddDataToCustomerSection;
use Yireo\GoogleTagManager2\SessionDataProvider\CustomerSessionDataProvider;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateCustomer;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\GoogleTagManager2\Test\Integration\Stub\NullCodeGroupRepositoryStub;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertInterceptorPluginIsRegistered;

/**
 * @magentoAppArea frontend
 */
class AddDataToCustomerSectionTest extends PageTestCase
{
    use AssertInterceptorPluginIsRegistered;
    use CreateCustomer;

    public function testIfPluginIsRegisterd()
    {
        $this->assertInterceptorPluginIsRegistered(
            Customer::class,
            AddDataToCustomerSection::class,
            'Yireo_GoogleTagManager2::addDataToCustomerSection'
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testGuestGetsGuestGroupCode()
    {
        $gtmData = $this->getCustomerSectionData()['gtm'];

        $this->assertSame(0, $gtmData['customerLoggedIn']);
        $this->assertSame(0, $gtmData['customerId']);
        $this->assertSame(0, $gtmData['customerGroupId']);
        $this->assertSame('GUEST', $gtmData['customerGroupCode']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testLoggedInCustomerGetsUppercasedGroupCode()
    {
        $this->createCustomer();
        $this->loginCustomer();

        $gtmData = $this->getCustomerSectionData()['gtm'];

        $this->assertSame(1, $gtmData['customerLoggedIn']);
        $this->assertEquals(1, $gtmData['customerId']);
        $this->assertEquals(1, $gtmData['customerGroupId']);
        $this->assertSame('GENERAL', $gtmData['customerGroupCode']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testCustomGroupCodeIsUppercased()
    {
        $groupId = $this->createCustomerGroup('vip_customers');
        $this->createCustomer(1, ['group_id' => $groupId]);
        $this->loginCustomer();

        $gtmData = $this->getCustomerSectionData()['gtm'];

        $this->assertEquals($groupId, $gtmData['customerGroupId']);
        $this->assertSame('VIP_CUSTOMERS', $gtmData['customerGroupCode']);
    }

    /**
     * Proves PR #312: a customer group without a code must not break the customer section
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testNullGroupCodeDoesNotBreakPlugin()
    {
        $this->createCustomer();
        $this->loginCustomer();

        $plugin = $this->createPluginWithNullCodeGroupRepository();
        $subject = $this->objectManager->get(Customer::class);
        $result = $plugin->afterGetSectionData($subject, []);

        $this->assertSame(1, $result['gtm']['customerLoggedIn']);
        $this->assertEquals(1, $result['gtm']['customerId']);
        $this->assertEquals(1, $result['gtm']['customerGroupId']);
        $this->assertSame('', $result['gtm']['customerGroupCode']);
    }

    /**
     * Proves PR #312: without the fix, customer/section/load responds with a 400 and the section is lost
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testNullGroupCodeDoesNotBreakCustomerSectionLoad()
    {
        $this->createCustomer();
        $this->loginCustomer();

        $this->objectManager->addSharedInstance(
            $this->createPluginWithNullCodeGroupRepository(),
            AddDataToCustomerSection::class
        );

        $data = $this->loadCustomerSectionViaController();

        $this->assertSame('John', $data['customer']['firstname'] ?? null);
        $this->assertSame(1, $data['customer']['gtm']['customerLoggedIn']);
        $this->assertSame('', $data['customer']['gtm']['customerGroupCode']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testMissingGroupDoesNotBreakCustomerSectionLoad()
    {
        $this->createCustomer();
        $this->loginCustomer();

        $missingGroupId = 987654;
        $this->objectManager->get(CustomerSession::class)->setCustomerGroupId($missingGroupId);

        $data = $this->loadCustomerSectionViaController();

        $this->assertSame(1, $data['customer']['gtm']['customerLoggedIn']);
        $this->assertEquals(1, $data['customer']['gtm']['customerId']);
        $this->assertEquals($missingGroupId, $data['customer']['gtm']['customerGroupId']);
        $this->assertSame('', $data['customer']['gtm']['customerGroupCode']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testGtmEventsAreMergedOnceForLoggedInCustomer()
    {
        $this->createCustomer();
        $this->loginCustomer();

        $customerSessionDataProvider = $this->objectManager->get(CustomerSessionDataProvider::class);
        $customerSessionDataProvider->clear();
        $customerSessionDataProvider->add('foobar_event', ['event' => 'foobar']);

        $data = $this->getCustomerSectionData();
        $this->assertSame('foobar', $data['gtm_events']['foobar_event']['event']);
        $this->assertSame('GENERAL', $data['gtm']['customerGroupCode']);

        $data = $this->getCustomerSectionData();
        $this->assertEmpty($data['gtm_events']);
        $this->assertSame('GENERAL', $data['gtm']['customerGroupCode']);
    }

    private function getCustomerSectionData(): array
    {
        $sectionPool = $this->objectManager->get(SectionPool::class);
        return $sectionPool->getSectionsData(['customer'])['customer'];
    }

    private function loadCustomerSectionViaController(): array
    {
        $this->getRequest()->setParams(['sections' => 'customer']);
        $this->dispatch('customer/section/load');

        $body = (string)$this->getResponse()->getBody();
        $this->assertSame(200, $this->getResponse()->getHttpResponseCode(), $body);

        return $this->objectManager->get(SerializerInterface::class)->unserialize($body);
    }

    private function createPluginWithNullCodeGroupRepository(): AddDataToCustomerSection
    {
        return $this->objectManager->create(AddDataToCustomerSection::class, [
            'groupRepository' => $this->objectManager->create(NullCodeGroupRepositoryStub::class),
        ]);
    }

    private function createCustomerGroup(string $code): int
    {
        /** @var GroupInterface $group */
        $group = $this->objectManager->get(GroupInterfaceFactory::class)->create();
        $group->setCode($code);
        $group->setTaxClassId(3);

        $group = $this->objectManager->get(GroupRepositoryInterface::class)->save($group);
        return (int)$group->getId();
    }
}
