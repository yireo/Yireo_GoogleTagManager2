<?php
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */

namespace Yireo\GoogleTagManager2\Test\Unit\Helper;

use Yireo\GoogleTagManager2\Helper\Data as DataHelper;
use Yireo\GoogleTagManager2\Test\Unit\Generic;

/**
 * Class DataTest
 *
 * @package Yireo\GoogleTagManager2\Test\Unit\Helper
 */
class DataTest extends Generic
{
    /**
     * @var DataHelper
     */
    protected $target;

    /**
     * Setup method
     */
    protected function setUp()
    {
        parent::setUp();

        $this->target = $this->objectManager->get(DataHelper::class);
    }

    /**
     * @test
     * @covers DataHelper::getConfigValue
     */
    public function testIsEnabled()
    {
        $this->assertEquals($this->target->isEnabled(), (bool)$this->getConfigValue('enabled'));
    }

    /**
     * @test
     * @covers DataHelper::getConfigValue
     */
    public function testIsDebug()
    {
        $this->assertEquals($this->target->isDebug(), (bool)$this->getConfigValue('debug'));
    }
}
