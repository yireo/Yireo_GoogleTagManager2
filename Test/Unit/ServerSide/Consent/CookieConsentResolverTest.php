<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide\Consent;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Consent\CookieConsentResolver;
use Yireo\GoogleTagManager2\ServerSide\Context\CookieReader;

class CookieConsentResolverTest extends TestCase
{
    public function testIsActiveFollowsConsentBanner(): void
    {
        $this->assertTrue($this->getResolver(true)->isActive(1));
        $this->assertFalse($this->getResolver(false)->isActive(1));
    }

    public function testGetConsentReadsCookie(): void
    {
        $consent = ['analytics' => true, 'marketing' => false, 'preferences' => false];
        $this->assertSame($consent, $this->getResolver(true, $consent)->getConsent());
        $this->assertNull($this->getResolver(true)->getConsent());
    }

    private function getResolver(bool $bannerEnabled, ?array $consent = null): CookieConsentResolver
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')
            ->with('googletagmanager2/consent/enabled')
            ->willReturn($bannerEnabled);

        $cookieReader = $this->createMock(CookieReader::class);
        $cookieReader->method('getConsent')->willReturn($consent);

        return new CookieConsentResolver($scopeConfig, $cookieReader);
    }
}
