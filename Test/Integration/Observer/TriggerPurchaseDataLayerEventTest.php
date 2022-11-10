<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\SessionDataProvider\CheckoutSessionDataProvider;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetOrder;

class TriggerPurchaseDataLayerEventTest extends TestCase
{
    use GetOrder;

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testEventExecution()
    {
        ObjectManager::getInstance()->get(CheckoutSessionDataProvider::class)->clear();
        $order = $this->getOrder();

        $eventManager = ObjectManager::getInstance()->get(ManagerInterface::class);
        $eventManager->dispatch('sales_order_place_after', ['order' => $order]);

        $data = ObjectManager::getInstance()->get(CheckoutSessionDataProvider::class)->get();
        $this->assertArrayHasKey('purchase_event', $data);
        $this->assertArrayHasKey('event', $data['purchase_event']);
        $this->assertEquals('purchase', $data['purchase_event']['event']);
    }

}