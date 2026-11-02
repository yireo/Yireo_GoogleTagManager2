<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

/**
 * @magentoAppArea frontend
 * @see https://github.com/yireo/Yireo_GoogleTagManager2/pull/198
 */
class ScriptConsentTest extends PageTestCase
{
    private const BLOCK_NAME = 'yireo_googletagmanager2.script-consent';

    protected function setUp(): void
    {
        if (!method_exists(Config::class, 'isConsent')) {
            $this->markTestSkipped('Consent support (PR #198) is not available');
        }

        parent::setUp();
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 1
     */
    public function testConsentBlockIsAddedToLayout()
    {
        $this->dispatch('/');

        $block = $this->objectManager->get(LayoutInterface::class)->getBlock(self::BLOCK_NAME);
        $this->assertNotFalse($block, 'Block "' . self::BLOCK_NAME . '" not found in layout');
        $this->assertStringEndsWith('script-consent.phtml', (string)$block->getTemplate());
        $this->assertInstanceOf(Config::class, $block->getConfig());
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/consent/privacy_statement_url /privacy-policy
     */
    public function testConsentBannerIsRenderedWhenEnabled()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString('id="yireo-cookie-consent-banner"', $body);
        foreach (['necessary', 'analytics', 'preferences', 'marketing'] as $mode) {
            $this->assertStringContainsString('id="yireo-consent-' . $mode . '"', $body);
        }

        $this->assertStringContainsString('Accept All', $body);
        $this->assertStringContainsString('Accept Selection', $body);
        $this->assertStringContainsString('Reject All', $body);
        $this->assertStringContainsString('href="/privacy-policy"', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 1
     */
    public function testNoPrivacyLinkWithoutUrl()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringContainsString('id="yireo-cookie-consent-banner"', $body);
        $this->assertDoesNotMatchRegularExpression('/<a[^>]+target="_blank"[^>]*>\s*Learn more/', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/consent/privacy_statement_url javascript:alert(1)
     */
    public function testPrivacyUrlIsEscaped()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringNotContainsString('href="javascript:alert(1)"', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 0
     */
    public function testConsentBannerIsNotRenderedWhenConsentDisabled()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $this->assertStringNotContainsString('yireo-cookie-consent-banner', $body);
        $this->assertStringNotContainsString('yireoGoogleTagManager2ConsentModes', $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 0
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 1
     */
    public function testConsentBlockIsAbsentWhenGtmDisabled()
    {
        $this->dispatch('/');

        $this->assertFalse($this->objectManager->get(LayoutInterface::class)->getBlock(self::BLOCK_NAME));
        $this->assertStringNotContainsString('yireo-cookie-consent-banner', $this->getResponse()->getBody());
    }

    /**
     * Consent Mode requires the "consent default" command to be in the dataLayer before the GTM container loads
     *
     * @see https://developers.google.com/tag-platform/security/guides/consent
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST
     * @magentoConfigFixture current_store googletagmanager2/consent/enabled 1
     */
    public function testConsentDefaultIsOutputBeforeGtmLoader()
    {
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();

        $consentPosition = strpos($body, 'yireoGoogleTagManager2ConsentModes');
        $gtmLoaderPosition = strpos($body, '/gtm.js?id=');
        $this->assertNotFalse($consentPosition, 'Consent script not found');
        $this->assertNotFalse($gtmLoaderPosition, 'GTM loader not found');
        $this->assertLessThan($gtmLoaderPosition, $consentPosition, 'Consent default is output after the GTM loader');
    }
}
