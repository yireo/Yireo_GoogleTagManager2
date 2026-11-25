<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

/**
 * @magentoAppArea frontend
 */
class ScriptCookieRestrictionTest extends PageTestCase
{
    private const GATE = 'cookieRestriction';

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 0
     * @magentoConfigFixture current_store web/cookie/cookie_restriction 1
     */
    public function testContainerIsGatedByCookieRestriction()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString('/gtm.js?id=', $body);
        $this->assertMatchesRegularExpression(
            '/' . self::GATE . ' = \{"name":"user_allowed_save_cookie","websiteId":1\}/',
            $body
        );
        $this->assertStringNotContainsString('/ns.html?id=', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 0
     * @magentoConfigFixture current_store web/cookie/cookie_restriction 0
     */
    public function testContainerIsNotGatedWithoutCookieRestriction()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString('/gtm.js?id=', $body);
        $this->assertStringContainsString(self::GATE . ' = null', $body);
        $this->assertStringContainsString('/ns.html?id=GTM-TEST', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 1
     * @magentoConfigFixture current_store web/cookie/cookie_restriction 1
     */
    public function testContainerIsNotGatedWhenConsentBannerIsEnabled()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString(self::GATE . ' = null', $body);
        $this->assertStringContainsString('/ns.html?id=GTM-TEST', $body);
    }
}
