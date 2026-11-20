<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Layout;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The AddProductDetails plugin only adds product data to a product list when the layout
 * of that page declares the "yireo_googletagmanager2.product-details" block. Without it,
 * select_item events on that page are pushed without product data.
 */
class ProductDetailsBlockTest extends TestCase
{
    #[DataProvider('getProductListLayoutHandles')]
    public function testProductListPageDeclaresProductDetailsBlock(string $layoutHandle)
    {
        $layoutFile = dirname(__DIR__, 3) . '/view/frontend/layout/' . $layoutHandle . '.xml';
        $this->assertFileExists($layoutFile);

        $document = new DOMDocument();
        $this->assertTrue($document->load($layoutFile));

        $xpath = new DOMXPath($document);
        $blocks = $xpath->query('//block[@name="yireo_googletagmanager2.product-details"]');
        $this->assertSame(1, $blocks->length);

        $block = $blocks->item(0);
        $this->assertSame('Yireo_GoogleTagManager2::product/details.phtml', $block->getAttribute('template'));
        $this->assertSame('googletagmanager2/settings/enabled', $block->getAttribute('ifconfig'));

        $productDetails = $xpath->query('arguments/argument[@name="product_details"]', $block)->item(0);
        $this->assertNotNull($productDetails);
        $this->assertSame(
            'Yireo\GoogleTagManager2\DataLayer\Tag\Product\CurrentProduct',
            trim($productDetails->textContent)
        );

        $dataLayer = $xpath->query('arguments/argument[@name="data_layer"]', $block)->item(0);
        $this->assertNotNull($dataLayer);
        $this->assertSame('Yireo\GoogleTagManager2\ViewModel\DataLayer', trim($dataLayer->textContent));
    }

    public static function getProductListLayoutHandles(): array
    {
        return [
            'category page' => ['catalog_category_view'],
            'search result page' => ['catalogsearch_result_index'],
            'advanced search result page' => ['catalogsearch_advanced_result'],
        ];
    }
}
