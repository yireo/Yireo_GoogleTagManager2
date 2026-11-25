<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Layout;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Block\Script;

class ScriptBlockClassTest extends TestCase
{
    #[DataProvider('getLayoutFiles')]
    public function testModuleBlocksUseScriptBlockClass(string $layoutFile)
    {
        $document = new DOMDocument();
        $this->assertTrue($document->load($layoutFile));

        $xpath = new DOMXPath($document);
        $blocks = $xpath->query('//block[starts-with(@name, "yireo_googletagmanager2.")]');
        foreach ($blocks as $block) {
            $this->assertSame(
                Script::class,
                $block->getAttribute('class'),
                'Block "' . $block->getAttribute('name') . '" in ' . basename($layoutFile)
            );
        }
    }

    public static function getLayoutFiles(): array
    {
        $files = glob(dirname(__DIR__, 3) . '/view/frontend/layout/*.xml');
        $data = [];
        foreach ($files as $file) {
            $data[basename($file)] = [$file];
        }

        return $data;
    }
}
