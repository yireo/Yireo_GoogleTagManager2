<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\GoogleTagManager2\Util\ScriptFinder;
use Yireo\GoogleTagManager2\Util\SecureHtmlRendererStub;

class AddCspInlineScripts
{
    private ScriptFinder $scriptFinder;
    private SecureHtmlRendererStub $secureHtmlRendererStub;

    public function __construct(
        ScriptFinder $scriptFinder,
        SecureHtmlRendererStub $secureHtmlRendererStub
    ) {
        $this->scriptFinder = $scriptFinder;
        $this->secureHtmlRendererStub = $secureHtmlRendererStub;
    }

    public function afterToHtml(Template $block, $html): string
    {
        if (empty($html)) {
            return '';
        }

        if (false === strstr((string)$block->getNameInLayout(), 'yireo_googletagmanager2.')) {
            return $html;
        }

        $scripts = $this->scriptFinder->find($html);
        foreach ($scripts as $fullScript => $inlineJs) {
            $newScript = $this->secureHtmlRendererStub->renderTag('script', [], $inlineJs, false);
            $html = str_replace($fullScript, $newScript, $html);
        }

        return $html;
    }
}
