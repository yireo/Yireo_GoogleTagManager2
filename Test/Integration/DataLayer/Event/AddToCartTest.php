<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToCart;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\CreateProduct;

/**
 * @magentoAppArea frontend
 */
class AddToCartTest extends TestCase
{
    use CreateProduct;

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testValidDataLayerWithCart()
    {
        /** @var Product $product */
        $product = $this->createProduct(1);
        $addToCartEvent = ObjectManager::getInstance()->get(AddToCart::class);
        $data = $addToCartEvent->setProduct($product)->get();
        $this->assertCount(1, $data['ecommerce']['items']);
    }
}
