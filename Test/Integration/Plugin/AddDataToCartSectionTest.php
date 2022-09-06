<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Checkout\CustomerData\Cart;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Plugin\AddDataToCartSection;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertInterceptorPluginIsRegistered;

/**
 * @magentoAppArea frontend
 */
class AddDataToCartSectionTest extends TestCase
{
    use AssertInterceptorPluginIsRegistered;

    public function testIfPluginIsRegisterd()
    {
        $this->assertInterceptorPluginIsRegistered(
            Cart::class,
            AddDataToCartSection::class,
            'Yireo_GoogleTagManager2::addAdditionalDataToCartSection'
        );
    }
}
