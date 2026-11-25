<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Config\SecureUrlValidator;

class SecureUrlValidatorTest extends TestCase
{
    #[DataProvider('getValidUrls')]
    public function testValidUrls(string $url): void
    {
        $this->assertTrue((new SecureUrlValidator())->isValid($url), $url);
    }

    public static function getValidUrls(): array
    {
        return [
            ['https://www.googletagmanager.com'],
            ['https://sgtm.example.com'],
            ['https://SGTM.Example.COM'],
            ['https://sgtm.example.com/'],
            ['https://sgtm.example.com:8443'],
            ['https://sgtm.example.com:8443/path/to-container'],
            ['https://gtm-test123.uc.r.appspot.com'],
            ['https://xn--bcher-kva.example'],
            ['https://a1.b2.co'],
        ];
    }

    #[DataProvider('getInvalidUrls')]
    public function testInvalidUrls(string $url): void
    {
        $this->assertFalse((new SecureUrlValidator())->isValid($url), $url);
    }

    public static function getInvalidUrls(): array
    {
        return [
            'empty' => [''],
            'no scheme' => ['sgtm.example.com'],
            'http' => ['http://sgtm.example.com'],
            'ftp' => ['ftp://sgtm.example.com'],
            'protocol relative' => ['//sgtm.example.com'],
            'no host' => ['https://'],
            'path only' => ['https:///path'],
            'userinfo' => ['https://user@sgtm.example.com'],
            'userinfo with password' => ['https://user:pass@sgtm.example.com'],
            'query' => ['https://sgtm.example.com/?x=1'],
            'fragment' => ['https://sgtm.example.com/#a'],
            'whitespace' => ['https://sgtm.example.com/ path'],
            'quote in path' => ['https://sgtm.example.com/\'"'],
            'newline' => ["https://sgtm.example.com\n"],
            'backslash' => ['https://sgtm.example.com\\@evil.com'],
            'trailing dot' => ['https://sgtm.example.com.'],
            'leading dot' => ['https://.example.com'],
            'empty label' => ['https://sgtm..example.com'],
            'label with leading hyphen' => ['https://-sgtm.example.com'],
            'label with trailing hyphen' => ['https://sgtm-.example.com'],
            'underscore' => ['https://sg_tm.example.com'],
            'numeric tld' => ['https://example.123'],
            'port zero' => ['https://sgtm.example.com:0'],
            'port too high' => ['https://sgtm.example.com:65536'],
            'port empty' => ['https://sgtm.example.com:'],
            'localhost' => ['https://localhost'],
            'localhost with port' => ['https://localhost:8443'],
            'localhost uppercase' => ['https://LOCALHOST'],
            'localhost subdomain' => ['https://foo.localhost'],
            'local tld' => ['https://printer.local'],
            'localdomain tld' => ['https://host.localdomain'],
            'internal tld' => ['https://metadata.google.internal'],
            'arpa tld' => ['https://1.0.0.127.in-addr.arpa'],
            'single label' => ['https://intranet'],
            'single label with port' => ['https://intranet:8443/path'],
            'ipv4 loopback' => ['https://127.0.0.1'],
            'ipv4 private 10' => ['https://10.0.0.1'],
            'ipv4 private 192.168' => ['https://192.168.1.1'],
            'ipv4 private 172.16' => ['https://172.16.0.1'],
            'ipv4 link local metadata' => ['https://169.254.169.254/latest/meta-data'],
            'ipv4 public' => ['https://8.8.8.8'],
            'ipv4 with port' => ['https://8.8.8.8:443'],
            'ipv4 short' => ['https://127.1'],
            'ipv4 decimal' => ['https://2130706433'],
            'ipv4 hex' => ['https://0x7f000001'],
            'ipv4 octal' => ['https://0177.0.0.1'],
            'ipv6 bracketed loopback' => ['https://[::1]'],
            'ipv6 bracketed loopback with port' => ['https://[::1]:8443/path'],
            'ipv6 bracketed public' => ['https://[2001:4860:4860::8888]'],
            'ipv6 bracketed mapped ipv4' => ['https://[::ffff:127.0.0.1]'],
            'ipv6 unbracketed' => ['https://::1'],
            'ipv6 unbracketed public' => ['https://2001:4860:4860::8888'],
        ];
    }
}
