<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Config;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Config\Backend\CustomerAttributes;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Config\Source\CustomerAttributes as CustomerAttributesSource;
use Yireo\GoogleTagManager2\Exception\InvalidConfig;

class CustomerAttributesBackendTest extends TestCase
{
    #[DataProvider('getValidValues')]
    public function testValidValuesAreAccepted($value, $expected)
    {
        $backend = $this->createBackend($value);
        $backend->beforeSave();
        $this->assertSame($expected, $backend->getValue());
    }

    public static function getValidValues(): array
    {
        return [
            ['', ''],
            ['firstname', 'firstname'],
            ['firstname,lastname', 'firstname,lastname'],
            [['firstname', 'lastname'], ['firstname', 'lastname']],
        ];
    }

    #[DataProvider('getInvalidValues')]
    public function testInvalidValuesAreRejected($value)
    {
        $this->expectException(InvalidConfig::class);
        $this->createBackend($value)->beforeSave();
    }

    public static function getInvalidValues(): array
    {
        return [
            ['password_hash'],
            ['email'],
            ['firstname,password_hash'],
            [['firstname', 'rp_token']],
            ['foobar'],
        ];
    }

    public function testSourceModelDoesNotOfferDeniedAttributes()
    {
        $source = Bootstrap::getObjectManager()->get(CustomerAttributesSource::class);
        $values = array_column($source->toOptionArray(), 'value');
        $this->assertContains('firstname', $values);
        $this->assertSame([], array_values(array_intersect($values, Config::CUSTOMER_ATTRIBUTE_DENY_LIST)));
    }

    private function createBackend($value): CustomerAttributes
    {
        $backend = Bootstrap::getObjectManager()->create(CustomerAttributes::class);
        $backend->setPath('googletagmanager2/settings/customer_eav_attributes');
        $backend->setValue($value);

        return $backend;
    }
}
