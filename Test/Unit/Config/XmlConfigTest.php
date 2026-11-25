<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Config;

use DOMDocument;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\XmlConfig;
use Yireo\GoogleTagManager2\Config\XmlConfig\Converter;

class XmlConfigTest extends TestCase
{
    private const TAG_CLASS = 'Yireo\\GoogleTagManager2\\DataLayer\\Tag\\CurrencyCode';

    private const XML = <<<'XML'
<data_layer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <default>
        <item name="currency_code" xsi:type="object">Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode</item>
        <item name="static" xsi:type="string">foo</item>
    </default>
    <events>
        <event id="other_event">
            <item name="currency_code" xsi:type="object">Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode</item>
        </event>
        <event id="view_item">
            <item name="nested" xsi:type="array">
                <item name="currency_code" xsi:type="object">Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode</item>
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
            [Converter::TAG_CLASS_KEY => self::TAG_CLASS],
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
        $objectManager->method('get')->with(self::TAG_CLASS)->willReturn($tag);

        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturnCallback(function () use (&$storeId) {
            return $storeId;
        });
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new XmlConfig($dataStorage, $objectManager, $storeManager);
    }

    public function testResetStateClearsResolvedDefaults(): void
    {
        $xmlConfig = $this->createXmlConfig($storeId, $currencies, $calls);

        $storeId = 1;
        $xmlConfig->getDefault();
        $xmlConfig->_resetState();
        $xmlConfig->getDefault();

        $this->assertSame(2, $calls);
    }

    public function testNonTagClassIsNeverInstantiated(): void
    {
        $converted = $this->convertXml(<<<'XML'
<data_layer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <default>
        <item name="not_a_tag" xsi:type="object">Magento\Framework\DataObject</item>
        <item name="unknown" xsi:type="object">Test\UnknownClass</item>
    </default>
</data_layer>
XML);

        $dataStorage = $this->createStub(DataInterface::class);
        $dataStorage->method('get')->willReturnCallback(fn (string $path) => $converted[$path]);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->never())->method('get');
        $objectManager->expects($this->never())->method('create');

        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $default = (new XmlConfig($dataStorage, $objectManager, $storeManager))->getDefault();

        $this->assertSame([], $default[0]['not_a_tag']);
        $this->assertSame([], $default[0]['unknown']);
    }

    public function testConverterRejectsReservedItemName(): void
    {
        $this->expectException(LocalizedException::class);
        $this->convertXml(<<<'XML'
<data_layer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <default>
        <item name="__tag_class" xsi:type="string">Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode</item>
    </default>
</data_layer>
XML);
    }

    public function testConverterRejectsReservedItemNameInNestedArray(): void
    {
        $this->expectException(LocalizedException::class);
        $this->convertXml(<<<'XML'
<data_layer xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <events>
        <event id="view_item">
            <item name="nested" xsi:type="array">
                <item name="__tag_class" xsi:type="string">Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode</item>
            </item>
        </event>
    </events>
</data_layer>
XML);
    }

    private function convertXml(string $xml): array
    {
        $document = new DOMDocument();
        $document->loadXML($xml);

        return $this->createConverter()->convert($document);
    }

    private function createConverter(): Converter
    {
        return new Converter();
    }
}
