<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Event\Purchase;
use Yireo\GoogleTagManager2\SessionDataProvider\CheckoutSessionDataProvider;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetOrder;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class SuppressionTest extends TestCase
{
    use GetOrder;
    use ServerSideConfig;

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testBrowserPurchaseIsSuppressed(): void
    {
        $this->configureServerSide(['serverside_tracking/suppress_browser_purchase' => '1']);
        $this->assertSame([], $this->getBrowserPurchaseEvent());

        $purchase = ObjectManager::getInstance()->create(Purchase::class);
        $serverSideEvent = $purchase->setOrder($this->getOrder())->setServerSide()->get();
        $this->assertSame('purchase', $serverSideEvent['event']);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testBrowserPurchaseIsNotSuppressedWhenDisabled(): void
    {
        $this->configureServerSide(['serverside_tracking/suppress_browser_purchase' => '0']);
        $this->assertSame('purchase', $this->getBrowserPurchaseEvent()['event'] ?? null);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testBrowserPurchaseIsNotSuppressedWhenServerSidePurchaseIsDisabled(): void
    {
        $this->configureServerSide(['serverside_tracking/events' => 'refund']);
        $this->assertSame('purchase', $this->getBrowserPurchaseEvent()['event'] ?? null);
    }

    private function getBrowserPurchaseEvent(): array
    {
        $sessionDataProvider = ObjectManager::getInstance()->get(CheckoutSessionDataProvider::class);
        $sessionDataProvider->clear();

        ObjectManager::getInstance()->get(ManagerInterface::class)
            ->dispatch('sales_order_place_after', ['order' => $this->getOrder()]);

        return $sessionDataProvider->get()['purchase_event'] ?? [];
    }
}
