<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Consent\ConsentResolverInterface;

class ConfigTest extends TestCase
{
    private array $values = [];

    public function testIsEnabledRequiresCredentials(): void
    {
        $this->values = ['serverside_tracking/enabled' => '1'];
        $this->assertFalse($this->getConfig()->isEnabled());

        $this->values['serverside_tracking/measurement_id'] = 'G-TEST';
        $this->values['serverside_tracking/api_secret'] = 'encrypted';
        $this->assertTrue($this->getConfig()->isEnabled());
        $this->assertSame('secret', $this->getConfig()->getApiSecret());
    }

    public function testEndpointUrl(): void
    {
        $this->values = ['serverside_tracking/destination' => 'ga4', 'serverside_tracking/sgtm_url' => 'https://sgtm.example.com/'];
        $this->assertSame('https://www.google-analytics.com/mp/collect', $this->getConfig()->getEndpointUrl());
        $this->assertSame('https://www.google-analytics.com/debug/mp/collect', $this->getConfig()->getEndpointUrl(null, true));

        $this->values['serverside_tracking/destination'] = 'sgtm';
        $this->assertSame('https://sgtm.example.com/mp/collect', $this->getConfig()->getEndpointUrl());

        $this->values['serverside_tracking/sgtm_url'] = 'http://sgtm.example.com';
        $this->assertSame('https://www.google-analytics.com/mp/collect', $this->getConfig()->getEndpointUrl());

        $this->values['serverside_tracking/sgtm_url'] = '';
        $this->assertSame('https://www.google-analytics.com/mp/collect', $this->getConfig()->getEndpointUrl());
    }

    public function testIsSecureUrl(): void
    {
        $this->assertTrue(Config::isSecureUrl('https://sgtm.example.com'));
        $this->assertFalse(Config::isSecureUrl('http://sgtm.example.com'));
        $this->assertFalse(Config::isSecureUrl('https://'));
        $this->assertFalse(Config::isSecureUrl('sgtm.example.com'));
    }

    public function testEventsAndSuppression(): void
    {
        $this->values = [
            'serverside_tracking/enabled' => '1',
            'serverside_tracking/measurement_id' => 'G-TEST',
            'serverside_tracking/api_secret' => 'encrypted',
            'serverside_tracking/events' => 'refund,add_to_cart',
            'serverside_tracking/suppress_browser_purchase' => '1',
        ];

        $this->assertTrue($this->getConfig()->isEventEnabled('refund'));
        $this->assertFalse($this->getConfig()->isEventEnabled('purchase'));
        $this->assertFalse($this->getConfig()->suppressBrowserPurchase());

        $this->values['serverside_tracking/events'] = 'purchase';
        $this->assertTrue($this->getConfig()->suppressBrowserPurchase());
    }

    public function testRequireConsentOnlyWithActiveConsentResolver(): void
    {
        $this->values = ['serverside_tracking/require_consent' => '1'];
        $this->assertFalse($this->getConfig()->requireConsent());

        $this->values['consent/enabled'] = '1';
        $this->assertTrue($this->getConfig()->requireConsent());
    }

    public function testDefaults(): void
    {
        $this->values = [];
        $this->assertSame('order_place', $this->getConfig()->getPurchaseTrigger());
        $this->assertSame('cron', $this->getConfig()->getDispatchMode());
        $this->assertSame('ga4', $this->getConfig()->getDestination());
        $this->assertSame(1, $this->getConfig()->getMaxAttempts());
    }

    private function getConfig(): Config
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(function ($path) {
            return $this->values[str_replace('googletagmanager2/', '', $path)] ?? null;
        });
        $scopeConfig->method('isSetFlag')->willReturnCallback(function ($path) {
            return (bool)($this->values[str_replace('googletagmanager2/', '', $path)] ?? false);
        });

        $encryptor = $this->createMock(EncryptorInterface::class);
        $encryptor->method('decrypt')->willReturn('secret');

        $consentResolver = $this->createMock(ConsentResolverInterface::class);
        $consentResolver->method('isActive')->willReturnCallback(function () {
            return (bool)($this->values['consent/enabled'] ?? false);
        });

        return new Config($scopeConfig, $encryptor, $consentResolver);
    }
}
