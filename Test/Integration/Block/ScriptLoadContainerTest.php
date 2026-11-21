<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

/**
 * @magentoAppArea frontend
 * @see https://github.com/yireo/Yireo_GoogleTagManager2/pull/322
 */
class ScriptLoadContainerTest extends PageTestCase
{
    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     */
    public function testContainerIsLoadedByDefault()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString("event: 'gtm.js'", $body);
        $this->assertStringContainsString('/gtm.js?id=', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/settings/load_container 0
     */
    public function testContainerIsNotLoadedWhenDisabled()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString('YIREO_GOOGLETAGMANAGER2_ENABLED', $body);
        $this->assertStringContainsString("event: 'gtm.js'", $body);
        $this->assertStringNotContainsString('/gtm.js?id=', $body);
    }
}
