<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Catalog\Block\Product\ListProduct as ListProductBlock;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Plugin\GetProductsFromCategoryBlockPlugin;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertInterceptorPluginIsRegistered;

/**
 * @magentoAppArea frontend
 */
class GetProductsFromCategoryBlockPluginTest extends TestCase
{
    use AssertInterceptorPluginIsRegistered;

    public function testIfPluginIsRegisterd()
    {
        $this->assertInterceptorPluginIsRegistered(
            ListProductBlock::class,
            GetProductsFromCategoryBlockPlugin::class,
            'Yireo_GoogleTagManager2::getProductsFromCategoryBlockPlugin'
        );
    }
}
