<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Annotation\DataFixture;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Yireo\GoogleTagManager2\ViewModel\DataLayer;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertNonEmptyValueInArray;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertStoreConfigValueEquals;

class PageTestCase extends AbstractController
{
    use AssertStoreConfigValueEquals;
    use AssertNonEmptyValueInArray;

    protected ?ObjectManager $objectManager = null;
    protected ?LayoutInterface $layout = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
        $this->layout = $this->objectManager->get(LayoutInterface::class);

        //$indexerRegistry = $this->objectManager->create(IndexerRegistry::class);
        //$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();

        $this->loadStore();
    }

    protected function loadStore()
    {
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        try {
            $storeRepository->getById(1);
        } catch (NoSuchEntityException $e) {
            $fixtureResolver = Resolver::getInstance();
            $fixtureResolver->setCurrentFixtureType(DataFixture::ANNOTATION);
            $fixtureResolver->requireDataFixture('Magento/Store/_files/store.php');
        }

        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore(1);
    }

    protected function getDataFromDataLayer(): array
    {
        $dataLayer = $this->objectManager->get(DataLayer::class);
        return $dataLayer->getDataLayer();
    }

    protected function getEventsFromDataLayer(): array
    {
        $dataLayer = $this->objectManager->get(DataLayer::class);
        return $dataLayer->getDataLayerEvents();
    }

    protected function getEventFromDataLayerEvents(string $eventId, string $eventName): array
    {
        $this->assertDataLayerEventExists($eventId, $eventName);
        $events = $this->getEventsFromDataLayer();
        return $events[$eventId];
    }

    protected function loginCustomer()
    {
        $customerId = 1;
        $customerSession = $this->objectManager->get(CustomerSession::class);
        $customerSession->loginById($customerId);
        $this->assertTrue($customerSession->isLoggedIn());
    }

    protected function assertDataLayerContains(string $dataLayerKey)
    {
        $data = $this->getDataFromDataLayer();
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey($dataLayerKey, $data, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function assertDataLayerEquals($expectedValue, string $dataLayerKey)
    {
        $data = $this->getDataFromDataLayer();
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey($dataLayerKey, $data, json_encode($data, JSON_PRETTY_PRINT));
        $this->assertEquals($expectedValue, $data[$dataLayerKey]);
    }

    protected function assertDataLayerEventExists(string $eventId, string $eventName)
    {
        $events = $this->getEventsFromDataLayer();
        $this->assertArrayHasKey($eventId, $events, var_export($events, true));
        $event = $events[$eventId];
        $this->assertEquals($eventName, $event['event']);
    }

    protected function assertEnabledFlagIsWorking()
    {
        $this->assertStoreConfigValueEquals(1, 'googletagmanager2/settings/enabled', 'store');
    }

    protected function assertRequestActionName(string $expectedActionName)
    {
        $request = $this->objectManager->get(RequestInterface::class);
        $this->assertSame($expectedActionName, $request->getActionName());
    }


}
