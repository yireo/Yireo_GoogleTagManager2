<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Mockery;
use PHPUnit\Framework\TestCase;

use Yireo\GoogleTagManager2\Helper\Data;
use Magento\Framework\App\Helper\Context as HelperContext;
use Yireo\GoogleTagManager2\Config;

/**
 * Class DataTest
 *
 * @package Yireo\GoogleTagManager2\Test\Unit\Helper
 */
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
     * @return Data
     */
    private function getTarget(array $configData = []): Data
    {
        $helperContext = $this->getHelperContextMock();
        $config = $this->getConfigMock($configData);
        $target = new Data($helperContext, $config);
        return $target;
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
     * @param array $data
     *
     * @return Config
     */
    private function getConfigMock(array $configData = []): Config
    {
        $config = Mockery::mock(Config::class);

        if (isset($configData['debug']) && $configData['debug'] == true) {
            $config->allows()->isDebug()->andReturns(true);
        } else {
            $config->allows()->isDebug()->andReturns(false);
        }

        return $config;
    }
}
