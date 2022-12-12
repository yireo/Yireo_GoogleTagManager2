<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Checkout\Api\ShippingInformationManagementInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Plugin\TriggerAddShippingInfoDataLayerEvent;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertInterceptorPluginIsRegistered;

/**
 * @magentoAppArea webapi_rest
 */
class TriggerAddShippingInfoDataLayerEventTest extends TestCase
{
    use AssertInterceptorPluginIsRegistered;

    public function testIfPluginIsRegisterd()
    {
        $this->assertInterceptorPluginIsRegistered(
            ShippingInformationManagementInterface::class,
            TriggerAddShippingInfoDataLayerEvent::class,
            'Yireo_GoogleTagManager2::triggerAddShippingInfoDataLayerEvent'
        );
    }
}
