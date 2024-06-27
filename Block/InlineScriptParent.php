<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Yireo\GoogleTagManager2\Util\SecureHtmlRendererStub;

class InlineScriptParent extends AbstractBlock
{
    private SecureHtmlRendererStub $secureHtmlRenderer;

    public function __construct(
        SecureHtmlRendererStub $secureHtmlRenderer,
        Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->secureHtmlRenderer = $secureHtmlRenderer;
    }

    public function toHtml()
    {
        $html = parent::getChildHtml('inner');
        $html = str_replace('<script>', '', $html);
        $html = str_replace('</script>', '', $html);

        return $this->secureHtmlRenderer->renderTag('script', [], $html, false);
    }
}
