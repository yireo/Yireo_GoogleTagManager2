<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide\Context;

use Magento\Framework\Stdlib\CookieManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Context\CookieReader;

class CookieReaderTest extends TestCase
{
    public function testParseClientId(): void
    {
        $reader = $this->getReader();
        $this->assertSame('1234567890.1699999999', $reader->parseClientId('GA1.1.1234567890.1699999999'));
        $this->assertSame('1234567890.1699999999', $reader->parseClientId('GA1.2.1234567890.1699999999'));
        $this->assertNull($reader->parseClientId(''));
        $this->assertNull($reader->parseClientId('GA1.1.abc'));
        $this->assertNull($reader->parseClientId('GA1.1.abc.def'));
    }

    public function testParseSessionId(): void
    {
        $reader = $this->getReader();
        $this->assertSame('1699999999', $reader->parseSessionId('GS1.1.1699999999.3.1.1700000000.0.0.0'));
        $this->assertSame('1699999999', $reader->parseSessionId('GS2.1.s1699999999$o3$g1$t1700000000$j60$l0$h0'));
        $this->assertNull($reader->parseSessionId(''));
        $this->assertNull($reader->parseSessionId('GS1.1.foo'));
    }

    public function testParseConsent(): void
    {
        $reader = $this->getReader();
        $value = rawurlencode('{"analytics":true,"marketing":false}');
        $this->assertSame(
            ['analytics' => true, 'marketing' => false, 'preferences' => false],
            $reader->parseConsent($value)
        );
        $this->assertNull($reader->parseConsent(''));
        $this->assertNull($reader->parseConsent('not-json'));
    }

    public function testGetSessionIdUsesMeasurementIdSuffix(): void
    {
        $cookieManager = $this->createMock(CookieManagerInterface::class);
        $cookieManager->method('getCookie')->willReturnMap([
            ['_ga_ABC123', null, 'GS1.1.1699999999.3.1.1700000000.0.0.0'],
        ]);

        $reader = new CookieReader($cookieManager);
        $this->assertSame('1699999999', $reader->getSessionId('G-ABC123'));
        $this->assertNull($reader->getSessionId(''));
    }

    private function getReader(): CookieReader
    {
        return new CookieReader($this->createMock(CookieManagerInterface::class));
    }
}
