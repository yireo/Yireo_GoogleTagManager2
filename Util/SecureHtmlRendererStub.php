<?php declare(strict_types=1);
// phpcs:ignoreFile

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author    Jisse Reitsma <jisse@yireo.com>
 * @copyright 2024 Yireo (https://www.yireo.com/)
 * @license   Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Util;

if (class_exists('\Magento\Framework\View\Helper\SecureHtmlRenderer')) {
    // @phpstan-ignore-next-line
    class SecureHtmlRendererStub extends \Magento\Framework\View\Helper\SecureHtmlRenderer\Proxy {}
} else {
    class SecureHtmlRendererStub
    {
        public function renderTag(
            string $tagName,
            array $attributes,
            ?string $content = null,
            bool $textContent = true
        ): string {
            $htmlAttributes = '';
            if (!empty($attributes)) {
                $htmlAttributes .= ' ';
                foreach ($attributes as $attributeName => $attributeValue) {
                    $htmlAttributes .= $attributeName . '="' . $attributeValue . '"';
                }
            }

            $html = '<' . $tagName . $htmlAttributes . '>' . "\n";
            $html .= $content;
            $html .= "\n" . '</' . $tagName . '>' . "\n";

            return $html;
        }
    }
}
