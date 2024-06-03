<?php declare(strict_types=1);

namespace Tagging\GTM\Test\Integration\Plugin;

use Magento\Checkout\CustomerData\Cart;
use PHPUnit\Framework\TestCase;
use Tagging\GTM\Plugin\AddDataToCartSection;
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
            'Tagging_GTM::addAdditionalDataToCartSection'
        );
    }
}
