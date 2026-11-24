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

    public function testBeginCheckoutTemplateEscapesTags(): void
    {
        $beginCheckout = $this->getMockBuilder(BeginCheckout::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $beginCheckout->method('get')->willReturn([
            'event' => 'begin_checkout',
            'ecommerce' => ['coupon' => self::PAYLOAD],
        ]);

        $layout = ObjectManager::getInstance()->get(LayoutInterface::class);
        /** @var Template $block */
        $block = $layout->createBlock(Template::class);
        $block->setTemplate('Yireo_GoogleTagManager2::luma/script-begin-checkout.phtml');
        $block->setData('begin_checkout_event', $beginCheckout);
        $block->setData('data_layer_view_model', ObjectManager::getInstance()->get(DataLayer::class));
        $html = $block->toHtml();

        $this->assertStringContainsString('YIREO_GOOGLETAGMANAGER2_BEGIN_CHECKOUT', $html);
        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString('\u003Cscript\u003E', $html);
    }
}
