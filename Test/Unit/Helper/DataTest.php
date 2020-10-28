<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

use Yireo\GoogleTagManager2\Helper\Data;
use Magento\Framework\App\Helper\Context as HelperContext;
use Yireo\GoogleTagManager2\Config\Config;

class DataTest extends TestCase
{
    /**
     * Test whether the debug flag works
     */
    public function testDebug()
    {
        $target = $this->getTarget();
        $rt = $target->debug('foo', 'bar');
        $this->assertFalse($rt);

        $target = $this->getTarget(['debug' => 1]);
        $rt = $target->debug('foo', 'bar');
        $this->assertTrue($rt);
    }

    /**
     * @param array $configData
     * @return Data
     */
    private function getTarget(array $configData = []): Data
    {
        $helperContext = $this->getHelperContextMock();
        $config = $this->getConfigMock($configData);
        return new Data($helperContext, $config);
    }

    /**
     * @return HelperContext
     */
    private function getHelperContextMock(): HelperContext
    {
        $helper = new ObjectManagerHelper($this);
        return $helper->getObject(HelperContext::class);
    }

    /**
     * @param array $configData
     * @return Config
     */
    private function getConfigMock(array $configData = []): Config
    {
        $config = $this->createMock(Config::class);

        if (isset($configData['debug']) && $configData['debug'] == true) {
            $config->method('isDebug')->willReturn(true);
        } else {
            $config->method('isDebug')->willReturn(false);
        }

        return $config;
    }
}
