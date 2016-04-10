<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */

namespace Yireo\GoogleTagManager2\Test\Unit\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Yireo\GoogleTagManager2\Helper\Data
     */
    protected $targetHelper;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup method
     */
    protected function setUp()
    {
        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
        $app = $bootstrap->createApplication('Magento\Framework\App\Http');
        $bootstrap->run($app);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManager = ObjectManager::getInstance();

        $this->targetHelper = $this->objectManager->get('Yireo\GoogleTagManager2\Helper\Data');
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Helper\Data::getConfigValue
     */
    public function testIsEnabled()
    {
        $this->assertEquals($this->targetHelper->isEnabled(), (bool) $this->_getConfigValue('enabled'));
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Helper\Data::getConfigValue
     */
    public function testIsDebug()
    {
        $this->assertEquals($this->targetHelper->isDebug(), (bool) $this->_getConfigValue('debug'));
    }

    /**
     * @param $path
     * @param string $pathPrefix
     *
     * @return mixed
     */
    protected function _getConfigValue($path, $pathPrefix = 'googletagmanager2/settings')
    {
        if (strstr($path, '/') == false) {
            $path = $pathPrefix . '/' . $path;
        }

        return $this->_getScopeConfig()->getValue($path);
    }

    /**
     * @return mixed
     */
    protected function _getScopeConfig()
    {
        return $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
    }
}