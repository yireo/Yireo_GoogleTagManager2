<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\View\Layout;
use Magento\TestFramework\TestCase\AbstractController;
use Yireo\GoogleTagManager2\ViewModel\DataLayer;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertStoreConfigValueEquals;

class PageTestCase extends AbstractController
{
    use AssertStoreConfigValueEquals;

    protected ?ObjectManager $objectManager = null;
    protected ?Layout $layout = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
        $this->layout = $this->objectManager->get(Layout::class);
    }

    protected function assertDataLayerEquals($expectedValue, string $dataLayerKey)
    {
        $dataLayer = $this->objectManager->get(DataLayer::class);
        $data = $dataLayer->getDataLayer();
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey($dataLayerKey, $data, json_encode($data, JSON_PRETTY_PRINT));
        $this->assertEquals($expectedValue, $data[$dataLayerKey]);
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
