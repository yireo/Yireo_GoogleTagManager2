<?php

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Config;

use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Cookie\Helper\Cookie as CookieHelper;
use Yireo\GoogleTagManager2\Config\Config;

class ConfigTest extends TestCase
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @test
     * @covers Config::isEnabled
     */
    public function testIsEnabled()
    {
        $this->setScopeConfigValue('enabled', 1);
        $this->assertEquals(true, $this->getTarget()->isEnabled());

        $this->setScopeConfigValue('enabled', 0);
        $this->assertEquals(false, $this->getTarget()->isEnabled());
    }

    /**
     * @test
     * @covers Config::isDebug
     */
    public function testIsDebug()
    {
        $this->setScopeConfigValue('debug', 1);
        $this->assertEquals(true, $this->getTarget()->isDebug());

        $this->setScopeConfigValue('debug', 0);
        $this->assertEquals(false, $this->getTarget()->isDebug());
    }

    /**
     * @test
     * @covers Config::getId
     */
    public function testGetId()
    {
        $this->setScopeConfigValue('id', 42);
        $this->assertEquals(42, $this->getTarget()->getId());

        $this->setScopeConfigValue('id', null);
        $this->assertEquals(null, $this->getTarget()->getId());
    }

    /**
     * @return Config
     */
    private function getTarget(): Config
    {
        $scopeConfig = $this->getScopeConfigMock();
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $cookieHelper = $this->getCookieHelperMock();
        return new Config($scopeConfig, $storeManager, $cookieHelper);
    }

    /**
     * @return ScopeConfigInterface
     */
    private function getScopeConfigMock(): ScopeConfigInterface
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        foreach ($this->settings as $path => $value) {
            $scopeConfig->method('getValue')->with($path)->willReturn($value);
        }

        return $scopeConfig;
    }

    /**
     * @return CookieHelper
     */
    private function getCookieHelperMock(): CookieHelper
    {
        $cookieHelper = $this->createMock(CookieHelper::class);
        $cookieHelper->method('isCookieRestrictionModeEnabled')->willReturn(true);
        $cookieHelper->method('isUserNotAllowSaveCookie')->willReturn(false);
        return $cookieHelper;
    }

    /**
     * @param string $path
     * @param mixed $value
     */
    private function setScopeConfigValue(string $path, $value)
    {
        $path = 'googletagmanager2/settings/' . $path;
        $this->settings[$path] = $value;
    }
}
