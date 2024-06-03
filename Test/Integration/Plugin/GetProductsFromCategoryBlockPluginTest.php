<?php declare(strict_types=1);

namespace Tagging\GTM\Test\Integration\Plugin;

use Magento\Catalog\Block\Product\ListProduct as ListProductBlock;
use PHPUnit\Framework\TestCase;
use Tagging\GTM\Plugin\GetProductsFromCategoryBlockPlugin;
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
            'Tagging_GTM::getProductsFromCategoryBlockPlugin'
        );
    }
}
