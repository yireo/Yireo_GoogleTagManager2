<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide\Consent;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Consent\CookieConsentResolver;
use Yireo\GoogleTagManager2\ServerSide\Context\CookieReader;

class CookieConsentResolverTest extends TestCase
{
    private const ALL_GRANTED = ['analytics' => true, 'marketing' => true, 'preferences' => true];
    private const ALL_DENIED = ['analytics' => false, 'marketing' => false, 'preferences' => false];

    public function testIsActiveFollowsConsentBanner(): void
    {
        $this->assertTrue($this->getResolver(true)->isActive(1));
        $this->assertFalse($this->getResolver(false)->isActive(1));
    }

    public function testIsActiveFollowsCookieRestrictionMode(): void
    {
        $this->assertTrue($this->getResolver(false, null, true)->isActive(1));
        $this->assertTrue($this->getResolver(true, null, true)->isActive(1));
    }

    public function testGetConsentReadsCookie(): void
    {
        $consent = ['analytics' => true, 'marketing' => false, 'preferences' => false];
        $this->assertSame($consent, $this->getResolver(true, $consent)->getConsent());
        $this->assertNull($this->getResolver(true)->getConsent());
    }

    public function testGetConsentPrefersConsentBannerOverCookieRestriction(): void
    {
        $consent = ['analytics' => false, 'marketing' => false, 'preferences' => true];
        $this->assertSame($consent, $this->getResolver(true, $consent, true, true)->getConsent(1));
    }

    public function testGetConsentFallsBackToCookieRestriction(): void
    {
        $this->assertSame(self::ALL_GRANTED, $this->getResolver(false, null, true, true)->getConsent(1));
        $this->assertSame(self::ALL_DENIED, $this->getResolver(false, null, true, false)->getConsent(1));
        $this->assertNull($this->getResolver(false, null, true, null)->getConsent(1));
    }

    public function testGetConsentReadsModuleCookieWithoutCookieRestriction(): void
    {
        $consent = ['analytics' => true, 'marketing' => false, 'preferences' => false];
        $this->assertSame($consent, $this->getResolver(false, $consent, false, false)->getConsent(1));
        $this->assertNull($this->getResolver(false, null, false, true)->getConsent(1));
    }

    private function getResolver(
        bool $bannerEnabled,
        ?array $consent = null,
        bool $restrictionMode = false,
        ?bool $restrictionAllowed = null
    ): CookieConsentResolver {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturnMap([
            ['googletagmanager2/consent/enabled', 'store', 1, $bannerEnabled],
            ['googletagmanager2/consent/enabled', 'store', null, $bannerEnabled],
            ['web/cookie/cookie_restriction', 'store', 1, $restrictionMode],
            ['web/cookie/cookie_restriction', 'store', null, $restrictionMode],
        ]);

        $cookieReader = $this->createMock(CookieReader::class);
        $cookieReader->method('getConsent')->willReturn($consent);
        $cookieReader->method('isCookieRestrictionAllowed')->with(3)->willReturn($restrictionAllowed);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn(3);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new CookieConsentResolver($scopeConfig, $cookieReader, $storeManager);
    }
}
