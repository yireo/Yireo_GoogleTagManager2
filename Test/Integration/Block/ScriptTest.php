<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Yireo\GoogleTagManager2\DataLayer\Event\BeginCheckout;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertContainerInLayout;

/**
 * @magentoAppArea frontend
 */
class ScriptTest extends PageTestCase
{
    use AssertContainerInLayout;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     */
    public function testValidBlockContent()
    {
        $this->assertEnabledFlagIsWorking();

        $this->layout->getUpdate()->addPageHandles(['empty', '1column']);
        $this->layout->generateXml();

        $this->dispatch('/');

        $this->assertContainerInLayout('before.body.end');
        $this->assertStringContainsString('Yireo_GoogleTagManager2', $this->layout->getUpdate()->asString());

        $body = $this->getResponse()->getBody(); // @phpstan-ignore-line
        $this->assertTrue((bool)strpos($body, 'yireoGoogleTagManager'), 'Script not found in HTML body: ' . $body);
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     */
    public function testCspNonceInScript()
    {
        $this->assertEnabledFlagIsWorking();

        $this->layout->getUpdate()->addPageHandles(['empty', '1column']);
        $this->layout->generateXml();

        $block = $this->layout->createBlock(Template::class);
        $block->setNameInLayout('yireo_googletagmanager2.hyva_checkout.data-layer.component');
        $block->setTemplate('Yireo_GoogleTagManager2::hyva_checkout/data-layer.phtml');
        $block->setData('begin_checkout_event', ObjectManager::getInstance()->get(BeginCheckout::class));

        $html = $block->toHtml();
        $this->assertStringContainsString('nonce="', $html);
    }
}
