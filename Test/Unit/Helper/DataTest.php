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

class DataTest extends \Yireo\GoogleTagManager2\Test\Unit\Generic
{
    /**
     * @var \Yireo\GoogleTagManager2\Helper\Data
     */
    protected $target;

    /**
     * Setup method
     */
    protected function setUp()
    {
        parent::setUp();

        $this->target = $this->objectManager->get('Yireo\GoogleTagManager2\Helper\Data');
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Helper\Data::getConfigValue
     */
    public function testIsEnabled()
    {
        $this->assertEquals($this->target->isEnabled(), (bool) $this->_getConfigValue('enabled'));
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Helper\Data::getConfigValue
     */
    public function testIsDebug()
    {
        $this->assertEquals($this->target->isDebug(), (bool) $this->_getConfigValue('debug'));
    }
}