<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\BeginCheckout;
use Yireo\GoogleTagManager2\ViewModel\Commons;
use Yireo\GoogleTagManager2\ViewModel\DataLayer;

/**
 * @magentoAppArea frontend
 */
class JsonHexTagTest extends TestCase
{
    private const PAYLOAD = '<!--<script>alert(1)</script>';

    public function testDataLayerToJsonEscapesTags(): void
    {
        $dataLayer = ObjectManager::getInstance()->get(DataLayer::class);
        $json = $dataLayer->toJson(['page_title' => self::PAYLOAD]);

        $this->assertStringNotContainsString('<', $json);
        $this->assertStringContainsString('\u003C!--\u003Cscript\u003E', $json);
    }

    public function testCommonsConfigurationJsonEscapesTags(): void
    {
        $dataLayer = $this->getMockBuilder(DataLayer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDataLayer'])
            ->getMock();
        $dataLayer->method('getDataLayer')->willReturn(['page_path' => '/' . self::PAYLOAD]);

        $commons = ObjectManager::getInstance()->create(Commons::class, ['dataLayer' => $dataLayer]);
        $json = $commons->getConfigurationAsJson();

        $this->assertStringNotContainsString('<', $json);
        $this->assertStringContainsString('\u003Cscript\u003E', $json);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id GTM-TEST123
     */
    public function testBeginCheckoutBlockFromLayoutEscapesTags(): void
    {
        $beginCheckout = $this->getMockBuilder(BeginCheckout::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $beginCheckout->method('get')->willReturn([
            'event' => 'begin_checkout',
            'ecommerce' => ['coupon' => self::PAYLOAD],
        ]);

        $objectManager = ObjectManager::getInstance();
        $layout = $objectManager->create(LayoutInterface::class);
        $layout->getUpdate()->addHandle('default');
        $layout->getUpdate()->addHandle('checkout_index_index');
        $layout->getUpdate()->load();
        $nodes = $layout->getUpdate()->asSimplexml()->xpath(
            '//block[@name="yireo_googletagmanager2.script-begin-checkout"]'
        );
        $this->assertNotEmpty($nodes, 'Begin checkout block not found in checkout layout');
        $blockNode = $nodes[0];

        $block = $layout->createBlock((string)$blockNode['class'] ?: Template::class);
        $block->setTemplate((string)$blockNode['template']);
        foreach ($blockNode->xpath('arguments/argument') as $argument) {
            $className = trim((string)$argument);
            if (class_exists($className)) {
                $block->setData((string)$argument['name'], $objectManager->get($className));
            }
        }

        $this->assertInstanceOf(DataLayer::class, $block->getData('data_layer_view_model'));
        $block->setData('begin_checkout_event', $beginCheckout);
        $html = $block->toHtml();

        $this->assertStringContainsString('YIREO_GOOGLETAGMANAGER2_BEGIN_CHECKOUT', $html);
        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString('\u003Cscript\u003E', $html);
        $this->assertSame(1, preg_match('#BEGIN_CHECKOUT = (\{.*\});#s', $html, $matches));
        $this->assertSame(self::PAYLOAD, json_decode($matches[1], true)['ecommerce']['coupon']);
    }

    public function testDataLayerToJsonSubstitutesInvalidUtf8(): void
    {
        $dataLayer = ObjectManager::getInstance()->get(DataLayer::class);
        $json = $dataLayer->toJson(['page_title' => "Caf\xE9"]);

        $this->assertSame(['page_title' => "Caf\u{FFFD}"], json_decode($json, true));
    }

    public function testDataLayerToJsonFallsBackToEmptyObject(): void
    {
        $dataLayer = ObjectManager::getInstance()->get(DataLayer::class);

        $this->assertSame('{}', $dataLayer->toJson(['value' => NAN]));
    }

    public function testDataLayerEventChunksSkipEventsThatCanNotBeEncoded(): void
    {
        $dataLayer = $this->getMockBuilder(DataLayer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDataLayerEvents'])
            ->getMock();
        $dataLayer->method('getDataLayerEvents')->willReturn([
            'broken' => ['event' => 'broken', 'value' => NAN],
            'view_cart' => ['event' => 'view_cart'],
        ]);

        $this->assertSame(['{"event":"view_cart"}'], $dataLayer->getDataLayerEventsAsJsonChunks());
    }
}
