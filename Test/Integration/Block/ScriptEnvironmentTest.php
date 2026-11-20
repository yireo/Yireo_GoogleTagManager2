<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Magento\Framework\Escaper;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

/**
 * @magentoAppArea frontend
 * @see https://github.com/yireo/Yireo_GoogleTagManager2/issues/307
 */
class ScriptEnvironmentTest extends PageTestCase
{
    private const QUERY = '&gtm_auth=Ab_c-12&gtm_preview=env-6&gtm_cookies_win=x';

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-ONE,GTM-TWO
     * @magentoConfigFixture current_store googletagmanager2/settings/environment_auth Ab_c-12
     * @magentoConfigFixture current_store googletagmanager2/settings/environment_preview env-6
     */
    public function testEnvironmentIsOnlyAddedToFirstContainer()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $escapedQuery = $this->objectManager->get(Escaper::class)->escapeJs(self::QUERY);
        $this->assertSame(1, substr_count($body, "dl + '" . $escapedQuery . "';"), 'GTM loader without environment');
        $this->assertSame(1, substr_count($body, "'/gtm.js?id=' + i + dl;"), 'Second GTM loader should not contain the environment');

        $this->assertStringContainsString('ns.html?id=GTM-ONE&amp;gtm_auth=Ab_c-12&amp;gtm_preview=env-6&amp;gtm_cookies_win=x"', $body);
        $this->assertStringContainsString('ns.html?id=GTM-TWO"', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-ONE
     */
    public function testNoEnvironmentWhenNotConfigured()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString("'/gtm.js?id=' + i + dl;", $body);
        $this->assertStringContainsString('ns.html?id=GTM-ONE"', $body);
        $this->assertStringNotContainsString('gtm_auth', $body);
        $this->assertStringNotContainsString('gtm_preview', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-ONE
     * @magentoConfigFixture current_store googletagmanager2/settings/environment_auth abc'+alert(1)+'
     * @magentoConfigFixture current_store googletagmanager2/settings/environment_preview env-6
     */
    public function testInvalidEnvironmentIsIgnored()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString("'/gtm.js?id=' + i + dl;", $body);
        $this->assertStringNotContainsString('gtm_auth', $body);
        $this->assertStringNotContainsString('alert(1)', $body);
    }
}
