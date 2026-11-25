<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Config;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Config\Backend\ContainerId;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class ContainerIdBackendTest extends TestCase
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
            ['GTM-ABC123', 'GTM-ABC123'],
            [' GTM-ABC123 ', 'GTM-ABC123'],
            ['GTM-ONE, GTM-TWO', 'GTM-ONE, GTM-TWO'],
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
            ['test'],
            ['GTM-'],
            ['gtm-abc123'],
            ['GTM-ABC123,test'],
            ['GTM-ABC123"><script>alert(1)</script>'],
            ["GTM-ABC\n123"],
            ['GTM-ABC&id=GTM-XYZ'],
        ];
    }

    private function createBackend(string $value): ContainerId
    {
        $backend = Bootstrap::getObjectManager()->create(ContainerId::class);
        $backend->setPath('googletagmanager2/settings/id');
        $backend->setValue($value);

        return $backend;
    }
}
