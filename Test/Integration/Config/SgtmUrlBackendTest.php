<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Config;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Config\Backend\SgtmUrl;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class SgtmUrlBackendTest extends TestCase
{
    #[DataProvider('getValidValues')]
    public function testValidValuesAreAccepted(string $value, string $expected)
    {
        $backend = $this->createBackend($value);
        $backend->beforeSave();
        $this->assertSame($expected, $backend->getValue());
    }

    public static function getValidValues(): array
    {
        return [
            ['', ''],
            [' https://sgtm.example.com ', 'https://sgtm.example.com'],
            ['https://example.com/sgtm/', 'https://example.com/sgtm/'],
        ];
    }

    #[DataProvider('getInvalidValues')]
    public function testInvalidValuesAreRejected(string $value)
    {
        $this->expectException(InvalidConfig::class);
        $this->createBackend($value)->beforeSave();
    }

    public static function getInvalidValues(): array
    {
        return [
            ['http://sgtm.example.com'],
            ['sgtm.example.com'],
            ['https://'],
            ['ftp://sgtm.example.com'],
            ['https://127.0.0.1'],
            ['https://169.254.169.254/latest/meta-data'],
            ['https://[::1]:8443'],
            ['https://localhost'],
            ['https://intranet'],
            ['https://metadata.google.internal'],
        ];
    }

    private function createBackend(string $value): SgtmUrl
    {
        $backend = Bootstrap::getObjectManager()->create(SgtmUrl::class);
        $backend->setPath('googletagmanager2/serverside_tracking/sgtm_url');
        $backend->setValue($value);

        return $backend;
    }
}
