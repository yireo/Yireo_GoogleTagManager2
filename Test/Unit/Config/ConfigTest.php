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

use Magento\Framework\App\State as AppState;
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
     * @var string
     */
    private $deployMode = AppState::MODE_DEVELOPER;

    /**
     * @test
     * @covers Config::isEnabled
     */
    public function testIsEnabled()
    {
        $this->setScopeConfigValue('enabled', 0);
        $this->assertFalse($this->getTarget()->isEnabled());

        $this->deployMode = AppState::MODE_DEVELOPER;
        $this->setScopeConfigValue('id', 'dummy');
        $this->setScopeConfigValue('enabled', 1);
        $this->assertTrue($this->getTarget()->isEnabled());

        $this->deployMode = AppState::MODE_PRODUCTION;
        $this->setScopeConfigValue('id', 'dummy');
        $this->setScopeConfigValue('enabled', 1);
        $this->assertFalse($this->getTarget()->isEnabled());

        $this->deployMode = AppState::MODE_PRODUCTION;
        $this->setScopeConfigValue('id', 'GTM-1234');
        $this->setScopeConfigValue('enabled', 1);
        $this->assertTrue($this->getTarget()->isEnabled());
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
        $appState = $this->getAppState();
        return new Config($scopeConfig, $storeManager, $cookieHelper, $appState);
    }

    /**
     * @return ScopeConfigInterface
     */
    private function getScopeConfigMock(): ScopeConfigInterface
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->will($this->returnCallback(function($path) {
            return $this->settings[$path];
        }));

        /*
        $valueMap = [];
        foreach ($this->settings as $path => $value) {
            $valueMap[] = [$path, 'dfgdf', 'fsdfs', $value];
        }

        $scopeConfig->expects($this->any())->method('getValue')->will($this->returnValueMap($valueMap));
        */
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
     * @return AppState
     */
    private function getAppState(): AppState
    {
        $appState = $this->createMock(AppState::class);
        $appState->method('getMode')->willReturn($this->deployMode);
        return $appState;
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
