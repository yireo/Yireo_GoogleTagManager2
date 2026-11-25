<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Block\Script;

/**
 * @magentoAppArea frontend
 */
class AddCspInlineScriptsTest extends TestCase
{
    private const PLUGIN_NAME = 'Yireo_GoogleTagManager2::addCspInlineScripts';

    public function testPluginIsOnlyConfiguredOnScriptBlock(): void
    {
        $pluginList = ObjectManager::getInstance()->get(PluginList::class);

        $this->assertNotContains(self::PLUGIN_NAME, $this->getAfterPlugins($pluginList, Template::class));
        $this->assertContains(self::PLUGIN_NAME, $this->getAfterPlugins($pluginList, Script::class));
    }

    public function testPlainTemplateWithModuleNameIsUntouched(): void
    {
        $html = $this->render(Template::class, 'yireo_googletagmanager2.foo');
        $this->assertStringContainsString('<script>', $html);
        $this->assertStringNotContainsString('nonce=', $html);
    }

    public function testScriptBlockWithOtherNameIsUntouched(): void
    {
        $html = $this->render(Script::class, 'foo.yireo_googletagmanager2.bar');
        $this->assertStringNotContainsString('nonce=', $html);
    }

    public function testScriptBlockWithModuleNameGetsNonce(): void
    {
        $html = $this->render(Script::class, 'yireo_googletagmanager2.foo');
        $this->assertMatchesRegularExpression('/<script nonce="[^"]+">/', $html);
    }

    private function getAfterPlugins(PluginList $pluginList, string $type): array
    {
        $next = (array)$pluginList->getNext($type, 'toHtml');

        return (array)($next[DefinitionInterface::LISTENER_AFTER] ?? []);
    }

    private function render(string $class, string $name): string
    {
        $layout = ObjectManager::getInstance()->get(LayoutInterface::class);
        /** @var Template $block */
        $block = $layout->createBlock($class, $name . '-' . uniqid());
        $block->setTemplate('Yireo_GoogleTagManager2::hyva/script-pusher.phtml');

        return (string)$block->toHtml();
    }
}
