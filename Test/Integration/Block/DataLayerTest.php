<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\ViewModel\DataLayer;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertContainerInLayout;

/**
 * @magentoAppArea frontend
 */
class DataLayerTest extends PageTestCase
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

        $layout = ObjectManager::getInstance()->get(LayoutInterface::class);
        $layout->getUpdate()->addHandle('datalayer_default');

        $block = $layout->createBlock(Template::class);
        $block->setNameInLayout('yireo_googletagmanager2.data-layer');
        $block->setTemplate('Yireo_GoogleTagManager2::luma/data-layer.phtml');
        $block->setData('data_layer_view_model', ObjectManager::getInstance()->get(DataLayer::class));
        $block->setData('config', ObjectManager::getInstance()->get(Config::class));
        $html = $block->toHtml();

        $this->assertTrue((bool)strpos($html, 'yireoGoogleTagManagerPush'), 'Data layer not found in block output');
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     */
    public function testValidBodyContent()
    {
        $this->assertEnabledFlagIsWorking();

        $this->layout->getUpdate()->addPageHandles(['empty', '1column']);
        $this->layout->generateXml();

        $this->dispatch('/');
        $body = $this->getResponse()->getBody(); // @phpstan-ignore-line

        $this->assertContainerInLayout('before.body.end');

        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotFalse($block, 'Block "yireo_googletagmanager2.data-layer" is empty');

        $array = $this->layout->getUpdate()->asArray();
        $this->assertTrue((bool)strpos($body, 'yireoGoogleTagManagerPush'), 'Data layer not found in HTML body: '. var_export($array, true));
    }
}
