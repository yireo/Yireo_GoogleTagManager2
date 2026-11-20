<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Config;

use DOMDocument;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\XmlConfig;
use Yireo\GoogleTagManager2\Config\XmlConfig\Converter;

class XmlConfigTest extends TestCase
{
    private const XML = <<<'XML'
<data_layer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <default>
        <item name="currency_code" xsi:type="object">Test\CurrencyTag</item>
        <item name="static" xsi:type="string">foo</item>
    </default>
    <events>
        <event id="other_event">
            <item name="currency_code" xsi:type="object">Test\CurrencyTag</item>
        </event>
        <event id="view_item">
            <item name="nested" xsi:type="array">
                <item name="currency_code" xsi:type="object">Test\CurrencyTag</item>
            </item>
        </event>
    </events>
</data_layer>
XML;

    public function testConverterDoesNotEvaluateTags(): void
    {
        $document = new DOMDocument();
        $document->loadXML(self::XML);

        $converted = $this->createConverter()->convert($document);

        $this->assertStringNotContainsString('USD', json_encode($converted));
        $this->assertStringNotContainsString('EUR', json_encode($converted));
        $this->assertSame(
            [Converter::TAG_CLASS_KEY => 'Test\CurrencyTag'],
            $converted['default'][0]['currency_code']
        );
        $this->assertSame('foo', $converted['default'][0]['static']);
    }

    public function testTagsAreEvaluatedPerStoreAndNotReusedAcrossStores(): void
    {
        $xmlConfig = $this->createXmlConfig($storeId, $currencies);

        $storeId = 1;
        $this->assertSame('USD', $xmlConfig->getDefault()[0]['currency_code']);
        $this->assertSame('USD', $xmlConfig->getEvent('view_item')['nested']['currency_code']);

        $storeId = 2;
        $this->assertSame('EUR', $xmlConfig->getDefault()[0]['currency_code']);
        $this->assertSame('EUR', $xmlConfig->getEvent('view_item')['nested']['currency_code']);

        $storeId = 1;
        $this->assertSame('USD', $xmlConfig->getDefault()[0]['currency_code']);
        $this->assertSame('foo', $xmlConfig->getDefault()[0]['static']);
    }

    public function testDefaultsAreEvaluatedOncePerStore(): void
    {
        $xmlConfig = $this->createXmlConfig($storeId, $currencies, $calls);

        $storeId = 1;
        $xmlConfig->getDefault();
        $xmlConfig->getDefault();

        $this->assertSame(1, $calls);
    }

    public function testGetEventResolvesOnlyTheRequestedEvent(): void
    {
        $xmlConfig = $this->createXmlConfig($storeId, $currencies, $calls);

        $storeId = 1;
        $xmlConfig->getEvent('other_event');

        $this->assertSame(1, $calls);
    }

    private function createXmlConfig(?int &$storeId, ?array &$currencies, ?int &$calls = null): XmlConfig
    {
        $storeId = 1;
        $currencies = [1 => 'USD', 2 => 'EUR'];
        $calls = 0;

        $document = new DOMDocument();
        $document->loadXML(self::XML);
        $converted = $this->createConverter()->convert($document);

        $tag = new class ($storeId, $currencies, $calls) implements TagInterface {
            public function __construct(private ?int &$storeId, private array &$currencies, private int &$calls)
            {
            }

            public function get()
            {
                $this->calls++;
                return $this->currencies[$this->storeId];
            }
        };

        $dataStorage = $this->createStub(DataInterface::class);
        $dataStorage->method('get')->willReturnCallback(fn (string $path) => $converted[$path]);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('get')->with('Test\CurrencyTag')->willReturn($tag);

        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturnCallback(function () use (&$storeId) {
            return $storeId;
        });
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new XmlConfig($dataStorage, $objectManager, $storeManager);
    }

    private function createConverter(): Converter
    {
        $tag = $this->createStub(TagInterface::class);
        $tag->method('get')->willReturn('USD');
        $objectManager = $this->createStub(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturn($tag);

        $constructor = (new ReflectionClass(Converter::class))->getConstructor();
        $arguments = $constructor && $constructor->getNumberOfRequiredParameters() > 0 ? [$objectManager] : [];

        return new Converter(...$arguments);
    }
}
