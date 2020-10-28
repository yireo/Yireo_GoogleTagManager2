<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

class ScriptTest extends AbstractController
{
    /**
     * @var string
     */
    private $uri;

    /**
     * Setup method
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->configure();
    }

    /**
     *
     */
    public function testCanHandleGetRequests()
    {
        $this->getRequest()->setMethod('GET');
        $this->dispatch($this->uri);
        $this->assertSame(200, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * Test whether the page contains valid body content
     */
    public function testValidBodyContent()
    {
        $this->dispatch('checkout/index/cart');
        $body = $this->getResponse()->getBody();
        $this->assertTrue((bool)strpos($body, 'yireoGoogleTagManager'));
    }

    /**
     * Configure settings
     */
    private function configure()
    {
        $settings = [
            'googletagmanager2/settings/enabled' => 1,
            'googletagmanager2/settings/id' => 'dummy',
        ];

        $configValueFactory = $this->getObjectManager()->get(ValueFactory::class);

        foreach ($settings as $settingPath => $settingValue) {
            $configValueFactory->create()
                ->load($settingPath, 'path')
                ->setPath($settingPath)
                ->setValue($settingValue)
                ->save();
        }

        /** @var CacheManager $cacheManager */
        $cacheManager = $this->getObjectManager()->get(CacheManager::class);
        $cacheManager->clean(['config']);
    }

    /**
     * @return ObjectManagerInterface
     */
    private function getObjectManager()
    {
        return Bootstrap::getObjectManager();
    }
}
