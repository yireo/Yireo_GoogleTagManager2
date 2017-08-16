<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */

namespace Yireo\GoogleTagManager2\Test\Unit\Block;

/**
 * Class GenericTest
 *
 * @package Yireo\GoogleTagManager2\Test\Unit\Block
 */
class GenericTest extends \Yireo\GoogleTagManager2\Test\Unit\Generic
{
    /**
     * @var \Yireo\GoogleTagManager2\Block\Generic
     */
    protected $target;

    /**
     * Setup method
     */
    protected function setUp()
    {
        parent::setUp();

        $this->target = $this->objectManager->get('Yireo\GoogleTagManager2\Block\Generic');
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Block\Generic::isEnabled
     */
    public function testIsEnabled()
    {
        $this->assertEquals($this->target->isEnabled(), (bool)$this->_getConfigValue('enabled'));
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Block\Generic::isDebug
     */
    public function testIsDebug()
    {
        $this->assertEquals($this->target->isDebug(), (bool)$this->_getConfigValue('debug'));
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Block\Generic::addAttribute
     */
    public function testAddAttribute()
    {
        $this->target->addAttribute('foo', 'bar');
        $this->assertEquals($this->target->getAttributes(), array('foo' => 'bar'));
        $this->assertCount(1, $this->target->getAttributes());
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Block\Generic::getAttributesAsJson
     */
    public function testGetAttributesAsJson()
    {
        $data = array('foo' => 'bar');
        $this->target->addAttribute('foo', 'bar');
        $this->assertJsonStringEqualsJsonString($this->target->getAttributesAsJson(), json_encode($data));
    }

    /**
     * @test
     * @covers \Yireo\GoogleTagManager2\Block\Generic::getWebsiteName
     */
    /*public function testGetWebsiteName()
    {
        $scopeConfig = $this->_getScopeConfig();
        $scopeConfig->setData('general/store_information/name', 'Test Website');
        $this->assertNotEmpty($this->target->getWebsiteName());
    }*/
}
