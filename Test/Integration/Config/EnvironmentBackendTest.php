<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Config;

use Magento\Framework\App\Config\Value;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Config\Backend\EnvironmentAuth;
use Yireo\GoogleTagManager2\Config\Backend\EnvironmentPreview;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

/**
 * @see https://github.com/yireo/Yireo_GoogleTagManager2/issues/307
 */
class EnvironmentBackendTest extends TestCase
{
    #[DataProvider('getValidValues')]
    public function testValidValuesAreAccepted(string $className, string $value, string $expected)
    {
        $backend = $this->createBackend($className, $value);
        $backend->beforeSave();
        $this->assertSame($expected, $backend->getValue());
    }

    public static function getValidValues(): array
    {
        return [
            [EnvironmentAuth::class, '', ''],
            [EnvironmentAuth::class, ' Ab_c-12 ', 'Ab_c-12'],
            [EnvironmentPreview::class, '', ''],
            [EnvironmentPreview::class, 'env-6', 'env-6'],
        ];
    }

    #[DataProvider('getInvalidValues')]
    public function testInvalidValuesAreRejected(string $className, string $value)
    {
        $this->expectException(InvalidConfig::class);
        $this->createBackend($className, $value)->beforeSave();
    }

    public static function getInvalidValues(): array
    {
        return [
            [EnvironmentAuth::class, 'abc&gtm_preview=env-1'],
            [EnvironmentAuth::class, "abc'+alert(1)+'"],
            [EnvironmentPreview::class, '6'],
            [EnvironmentPreview::class, 'env-6&foo=bar'],
            [EnvironmentPreview::class, '<script>'],
        ];
    }

    private function createBackend(string $className, string $value): Value
    {
        $backend = Bootstrap::getObjectManager()->create($className);
        $backend->setPath('googletagmanager2/settings/environment');
        $backend->setValue($value);

        return $backend;
    }
}
