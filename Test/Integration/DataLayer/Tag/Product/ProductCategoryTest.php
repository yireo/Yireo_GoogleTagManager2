<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Tag\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Tag\Product\ProductCategory;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetCategory;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetProduct;
use Yireo\GoogleTagManager2\Util\CategoryProvider;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
 */
class ProductCategoryTest extends TestCase
{
    use GetProduct;
    use GetCategory;

    public function testGetReturnsFirstUsableCategoryName()
    {
        /** @var Product $product */
        $product = $this->getProductBySku('gtm-multi-category');
        $this->assertSame('GTM Parent', $this->createProductCategory()->setProduct($product)->get());
    }

    public function testGetPrefersCategorySetOnProduct()
    {
        /** @var Product $product */
        $product = $this->getProductBySku('gtm-multi-category');
        $product->setCategory($this->getCategoryByName('GTM Grandchild'));
        $this->assertSame('GTM Grandchild', $this->createProductCategory()->setProduct($product)->get());
    }

    public function testGetReturnsEmptyStringWithoutUsableCategory()
    {
        /** @var Product $product */
        $product = $this->getProductBySku('gtm-no-usable-category');
        $this->assertSame('', $this->createProductCategory()->setProduct($product)->get());
    }

    private function createProductCategory(): ProductCategory
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->create(ProductCategory::class, [
            'categoryProvider' => $objectManager->create(CategoryProvider::class),
        ]);
    }
}
