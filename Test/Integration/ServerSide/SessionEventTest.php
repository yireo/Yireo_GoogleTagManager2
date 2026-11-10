<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class SessionEventTest extends TestCase
{
    use ServerSideConfig;

    public function testCheckoutSessionEventIsPublished(): void
    {
        $this->configureServerSide();
        $this->clearOutbox();

        $event = ['event' => 'add_to_cart', 'ecommerce' => ['currency' => 'USD', 'value' => 5, 'items' => []]];
        $provider = ObjectManager::getInstance()->get(CheckoutSessionDataProviderInterface::class);
        $provider->add('add_to_cart_event', $event);
        $provider->add('add_to_cart_event', $event);
        $provider->add('purchase_event', ['event' => 'purchase']);

        $this->assertCount(2, $this->getOutboxRows('add_to_cart'));
        $this->assertCount(0, $this->getOutboxRows('purchase'));
    }

    public function testCustomerSessionEventIsPublished(): void
    {
        $this->configureServerSide();
        $this->clearOutbox();

        $provider = ObjectManager::getInstance()->get(CustomerSessionDataProviderInterface::class);
        $provider->add('sign_up_event', ['event' => 'sign_up', 'method' => 'Standard']);

        $rows = $this->getOutboxRows('sign_up');
        $this->assertCount(1, $rows);
        $payload = json_decode($rows[0]['payload'], true);
        $this->assertSame('Standard', $payload['events'][0]['params']['method']);
    }

    public function testNothingIsPublishedWhenDisabled(): void
    {
        $this->configureServerSide(['serverside_tracking/enabled' => '0']);
        $this->clearOutbox();

        $provider = ObjectManager::getInstance()->get(CustomerSessionDataProviderInterface::class);
        $provider->add('login_event', ['event' => 'login']);

        $this->assertCount(0, $this->getOutboxRows());
    }
}
