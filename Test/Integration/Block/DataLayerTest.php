<?php declare(strict_types=1);

namespace Tagging\GTM\Test\Integration\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Tagging\GTM\ViewModel\DataLayer;
use Tagging\GTM\Config\Config;
use Tagging\GTM\Test\Integration\PageTestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\Layout\AssertContainerInLayout;

/**
 * @magentoAppArea frontend
 */
class DataLayerTest extends PageTestCase
{
    use AssertContainerInLayout;

    /**
     * @magentoConfigFixture current_store GTM/settings/enabled 1
     * @magentoConfigFixture current_store GTM/settings/serverside_gtm_url gtm.tryforwarder.com
     */
    public function testValidBlockContent()
    {
        $this->assertEnabledFlagIsWorking();

        $layout = ObjectManager::getInstance()->get(LayoutInterface::class);
        $layout->getUpdate()->addHandle('datalayer_default');

        $block = $layout->createBlock(Template::class);
        $block->setNameInLayout('Tagging_GTM.data-layer');
        $block->setTemplate('Tagging_GTM::luma/data-layer.phtml');
        $block->setData('data_layer_view_model', ObjectManager::getInstance()->get(DataLayer::class));
        $block->setData('config', ObjectManager::getInstance()->get(Config::class));
        $html = $block->toHtml();

        $this->assertTrue((bool)strpos($html, 'googleTagManagerPush'), 'Data layer not found in block output');
    }

    /**
     * @magentoConfigFixture current_store GTM/settings/enabled 1
     * @magentoConfigFixture current_store GTM/settings/method 1
     * @magentoConfigFixture current_store GTM/settings/id test
     */
    public function testValidBodyContent()
    {
        $this->assertEnabledFlagIsWorking();

        $this->layout->getUpdate()->addPageHandles(['empty', '1column']);
        $this->layout->generateXml();

        $this->dispatch('/');
        $body = $this->getResponse()->getBody(); // @phpstan-ignore-line

        $this->assertContainerInLayout('before.body.end');

        $block = $this->layout->getBlock('Tagging_GTM.data-layer');
        $this->assertNotFalse($block, 'Block "Tagging_GTM.data-layer" is empty');

        $array = $this->layout->getUpdate()->asArray();
        $this->assertTrue((bool)strpos($body, 'googleTagManagerPush'), 'Data layer not found in HTML body: '. var_export($array, true));
    }
}
