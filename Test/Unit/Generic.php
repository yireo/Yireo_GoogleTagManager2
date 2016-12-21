<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */

namespace Yireo\GoogleTagManager2\Test\Unit;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class Generic extends \PHPUnit_Framework_TestCase
{
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