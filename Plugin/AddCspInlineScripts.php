<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;
use Yireo\CspUtilities\ViewModel\CspMarker;

class AddCspInlineScripts
{
    private ReplaceInlineScripts $replaceInlineScripts;
    private CspMarker $cspMarker;

    public function __construct(
        ReplaceInlineScripts $replaceInlineScripts,
        CspMarker $cspMarker
    ) {
        $this->replaceInlineScripts = $replaceInlineScripts;
        $this->cspMarker = $cspMarker;
    }

    public function beforeToHtml(Template $block): void
    {
        if ($this->isAllowed($block) && false === $block->hasData('csp_marker')) {
            $block->setData('csp_marker', $this->cspMarker);
        }
    }

    public function afterToHtml(Template $block, $html): string
    {
        if (false === $this->isAllowed($block)) {
            return (string)$html;
        }

        return $this->replaceInlineScripts->replaceMarked((string)$html);
    }

    private function isAllowed(Template $block): bool
    {
        return 0 === strpos((string)$block->getNameInLayout(), 'yireo_googletagmanager2.');
    }
}
