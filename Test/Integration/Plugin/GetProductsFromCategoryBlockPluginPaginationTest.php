<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetCategory;
use Yireo\GoogleTagManager2\Util\GetCurrentCategoryProducts;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class GetProductsFromCategoryBlockPluginPaginationTest extends TestCase
{
    use GetCategory;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/category_products 10
     * @magentoConfigFixture current_store catalog/frontend/list_mode grid
     * @magentoConfigFixture current_store catalog/frontend/grid_per_page_values 2,4
     * @magentoConfigFixture current_store catalog/frontend/grid_per_page 2
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     */
    public function testEarlyCallDoesNotBreakPagination()
    {
        $objectManager = ObjectManager::getInstance();
        $category = $this->getCategoryByName('Category 999');

        $layerResolver = $objectManager->get(LayerResolver::class);
        $layerResolver->create(LayerResolver::CATALOG_LAYER_CATEGORY);
        $layerResolver->get()->setCurrentCategory((int)$category->getId());

        $layout = $objectManager->get(LayoutInterface::class);
        $layout->createBlock(Toolbar::class, 'product_list_toolbar');

        /** @var ListProduct $listBlock */
        $listBlock = $layout->createBlock(ListProduct::class, 'category.products.list');
        $listBlock->setToolbarBlockName('product_list_toolbar');
        $listBlock->setCollection($category->getProductCollection()->addAttributeToSelect('*'));

        $collection = $listBlock->getLoadedProductCollection();
        $this->assertNotTrue($collection->isLoaded(), 'Product collection was loaded before pagination was applied');

        $listBlock->toHtml();

        $collection = $listBlock->getLoadedProductCollection();
        $this->assertGreaterThanOrEqual(3, $collection->getSize());
        $this->assertCount(2, $collection->getItems());

        $getCurrentCategoryProducts = $objectManager->get(GetCurrentCategoryProducts::class);
        $this->assertCount(2, $getCurrentCategoryProducts->getProducts());
    }
}
