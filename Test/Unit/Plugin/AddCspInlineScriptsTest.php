<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Plugin;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\TestCase;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;
use Yireo\CspUtilities\ViewModel\CspMarker;
use Yireo\GoogleTagManager2\Plugin\AddCspInlineScripts;

class AddCspInlineScriptsTest extends TestCase
{
    public function testSetsMarkerOnModuleBlock(): void
    {
        $cspMarker = new CspMarker();
        $block = $this->getBlock('yireo_googletagmanager2.script');

        $plugin = new AddCspInlineScripts($this->createMock(ReplaceInlineScripts::class), $cspMarker);
        $plugin->beforeToHtml($block);

        $this->assertSame($cspMarker, $block->getData('csp_marker'));
    }

    public function testSkipsMarkerOnOtherBlock(): void
    {
        $block = $this->getBlock('foo');

        $plugin = new AddCspInlineScripts($this->createMock(ReplaceInlineScripts::class), new CspMarker());
        $plugin->beforeToHtml($block);

        $this->assertFalse($block->hasData('csp_marker'));
    }

    public function testReplacesScriptsOfModuleBlock(): void
    {
        $replaceInlineScripts = $this->createMock(ReplaceInlineScripts::class);
        $replaceInlineScripts->expects($this->once())->method('replaceMarked')->with('<script></script>')->willReturn('replaced');

        $plugin = new AddCspInlineScripts($replaceInlineScripts, new CspMarker());

        $this->assertSame(
            'replaced',
            $plugin->afterToHtml($this->getBlock('yireo_googletagmanager2.script'), '<script></script>')
        );
    }

    public function testSkipsScriptsOfOtherBlock(): void
    {
        $replaceInlineScripts = $this->createMock(ReplaceInlineScripts::class);
        $replaceInlineScripts->expects($this->never())->method('replaceMarked');

        $plugin = new AddCspInlineScripts($replaceInlineScripts, new CspMarker());

        $this->assertSame('<script></script>', $plugin->afterToHtml($this->getBlock('foo'), '<script></script>'));
    }

    private function getBlock(string $name): Template
    {
        $dataObject = new DataObject();
        $block = $this->createMock(Template::class);
        $block->method('getNameInLayout')->willReturn($name);
        $block->method('getData')->willReturnCallback(fn ($key = '') => $dataObject->getData($key));
        $block->method('hasData')->willReturnCallback(fn ($key = '') => $dataObject->hasData($key));
        $block->method('setData')->willReturnCallback(function ($key, $value = null) use ($dataObject, &$block) {
            $dataObject->setData($key, $value);
            return $block;
        });

        return $block;
    }
}
