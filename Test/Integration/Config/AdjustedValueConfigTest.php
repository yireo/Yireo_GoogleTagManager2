<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Config;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Config\Config;

/**
 * @magentoAppArea adminhtml
 */
class AdjustedValueConfigTest extends TestCase
{
    /**
     * @dataProvider getConfigPaths
     */
    public function testDefaultValueIsDisabled(string $path)
    {
        $scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->assertSame('0', (string)$scopeConfig->getValue($path));
    }

    /**
     * @dataProvider getConfigPaths
     */
    public function testFieldExistsInAdmin(string $path)
    {
        $structure = ObjectManager::getInstance()->get(Structure::class);
        $this->assertArrayHasKey($path, $structure->getFieldPaths(), 'Field is missing in etc/adminhtml/system.xml');

        $field = $structure->getElementByConfigPath($path);
        $this->assertInstanceOf(Field::class, $field);
        $this->assertNotEmpty($field->getLabel());
        $this->assertTrue($field->showInStore(), 'Field should be configurable on StoreView level');
    }

    public function testDefaultsDoNotChangeAdjustedValueBehaviour()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $this->assertSame(0.0, $config->getMaxTransactionValue());
        $this->assertFalse($config->includeShippingInAdjustedValue());
    }

    public static function getConfigPaths(): array
    {
        return [
            ['googletagmanager2/settings/max_transaction_value'],
            ['googletagmanager2/settings/include_shipping_in_adjusted_value'],
        ];
    }
}
