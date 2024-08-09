<?php declare(strict_types=1);

namespace Tagging\GTM\Test\Integration\DataLayer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Tagging\GTM\DataLayer\Event\AddToCart;
use Tagging\GTM\Test\Integration\FixtureTrait\CreateProduct;

/**
 * @magentoAppArea frontend
 */
class AddToCartTest extends TestCase
{
    use CreateProduct;

    /**
     * @magentoConfigFixture current_store GTM/settings/enabled 1
     * @magentoConfigFixture current_store GTM/settings/serverside_gtm_url gtm.tryforwarder.com
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
