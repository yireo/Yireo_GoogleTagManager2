<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Plugin;

use ArrayIterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Category\CategorySize;
use Yireo\GoogleTagManager2\Plugin\GetProductsFromCategoryBlockPlugin;
use Yireo\GoogleTagManager2\Util\GetCurrentCategoryProducts;

class GetProductsFromCategoryBlockPluginTest extends TestCase
{
    public function testUnloadedCollectionIsNotLoaded()
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('isLoaded')->willReturn(false);
        $collection->expects($this->never())->method('getIterator');
        $collection->expects($this->never())->method('count');

        $getCurrentCategoryProducts = new GetCurrentCategoryProducts();
        $result = $this->getPlugin(10, $getCurrentCategoryProducts)
            ->afterGetLoadedProductCollection($this->createMock(ListProduct::class), $collection);

        $this->assertSame($collection, $result);
        $this->assertEmpty($getCurrentCategoryProducts->getProducts());
    }

    public function testLoadedCollectionIsLimitedToMaximumCategoryProducts()
    {
        $products = [];
        for ($i = 1; $i <= 5; $i++) {
            $product = $this->createMock(ProductInterface::class);
            $product->method('getId')->willReturn($i);
            $products[] = $product;
        }

        $collection = $this->createMock(Collection::class);
        $collection->method('isLoaded')->willReturn(true);
        $collection->method('getIterator')->willReturn(new ArrayIterator($products));
        $collection->method('count')->willReturn(count($products));

        $getCurrentCategoryProducts = new GetCurrentCategoryProducts();
        $this->getPlugin(3, $getCurrentCategoryProducts)
            ->afterGetLoadedProductCollection($this->createMock(ListProduct::class), $collection);

        $this->assertSame([1, 2, 3], array_keys($getCurrentCategoryProducts->getProducts()));
    }

    private function getPlugin(
        int $maximumCategoryProducts,
        GetCurrentCategoryProducts $getCurrentCategoryProducts
    ): GetProductsFromCategoryBlockPlugin {
        $config = $this->createMock(Config::class);
        $config->method('getMaximumCategoryProducts')->willReturn($maximumCategoryProducts);

        return new GetProductsFromCategoryBlockPlugin(new CategorySize(), $getCurrentCategoryProducts, $config);
    }
}
