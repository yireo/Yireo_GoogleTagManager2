<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Observer\StoreContextAndPublishPurchase;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetOrder;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class PurchaseTriggerTest extends TestCase
{
    use GetOrder;
    use ServerSideConfig;

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderPlaceTrigger(): void
    {
        $this->configureServerSide();
        $this->clearOutbox();
        $order = $this->getOrder();

        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $order]);
        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $order]);

        $rows = $this->getOutboxRows('purchase');
        $this->assertCount(1, $rows);
        $this->assertSame('purchase:' . $order->getIncrementId(), $rows[0]['dedup_key']);
        $this->assertSame('pending', $rows[0]['status']);

        $payload = json_decode($rows[0]['payload'], true);
        $this->assertSame('purchase', $payload['events'][0]['name']);
        $this->assertSame($order->getIncrementId(), $payload['events'][0]['params']['transaction_id']);
        $this->assertNotEmpty($payload['client_id']);
        $this->assertNotEmpty($payload['events'][0]['params']['items']);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderPlaceTriggerUsesGaCookies(): void
    {
        $_COOKIE['_ga'] = 'GA1.1.1111111111.1699999999';
        $_COOKIE['_ga_TEST123'] = 'GS1.1.1700000001.1.1.1700000002.0.0.0';
        $this->configureServerSide();
        $this->clearOutbox();
        $order = $this->getOrder();

        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $order]);
        unset($_COOKIE['_ga'], $_COOKIE['_ga_TEST123']);

        $payload = json_decode($this->getOutboxRows('purchase')[0]['payload'], true);
        $this->assertSame('1111111111.1699999999', $payload['client_id']);
        $this->assertSame('1700000001', $payload['events'][0]['params']['session_id']);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testDisabledEventIsNotPublished(): void
    {
        $this->configureServerSide(['serverside_tracking/events' => 'refund']);
        $this->clearOutbox();

        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $this->getOrder()]);
        $this->assertCount(0, $this->getOutboxRows('purchase'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testConsentRequired(): void
    {
        $this->configureServerSide(['serverside_tracking/require_consent' => '1', 'consent/enabled' => '1']);
        $this->clearOutbox();
        $order = $this->getOrder();

        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $order]);
        $this->assertCount(0, $this->getOutboxRows('purchase'));

        $_COOKIE['yireo_gtm2_consent'] = rawurlencode('{"analytics":true,"marketing":true}');
        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $order]);
        unset($_COOKIE['yireo_gtm2_consent']);

        $rows = $this->getOutboxRows('purchase');
        $this->assertCount(1, $rows);
        $payload = json_decode($rows[0]['payload'], true);
        $this->assertSame('GRANTED', $payload['consent']['ad_user_data']);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderStateTrigger(): void
    {
        $this->configureServerSide([
            'serverside_tracking/purchase_trigger' => 'order_state',
            'settings/order_states_for_purchase_event' => 'complete',
        ]);
        $this->clearOutbox();
        $order = $this->getOrder();

        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $order]);
        $order->setState(Order::STATE_PROCESSING);
        $this->dispatch('sales_order_save_after', ['order' => $order]);
        $this->assertCount(0, $this->getOutboxRows('purchase'));

        $order->setState(Order::STATE_COMPLETE);
        $this->dispatch('sales_order_save_after', ['order' => $order]);
        $this->dispatch('sales_order_save_after', ['order' => $order]);
        $this->assertCount(1, $this->getOutboxRows('purchase'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testInvoicePayTrigger(): void
    {
        $this->configureServerSide(['serverside_tracking/purchase_trigger' => 'invoice_pay']);
        $this->clearOutbox();
        $order = $this->getOrder();

        $this->dispatch('sales_model_service_quote_submit_success', ['order' => $order]);
        $this->assertCount(0, $this->getOutboxRows('purchase'));

        $invoice = ObjectManager::getInstance()->create(Invoice::class);
        $invoice->setOrder($order);
        $this->dispatch('sales_order_invoice_pay', ['invoice' => $invoice]);
        $this->assertCount(1, $this->getOutboxRows('purchase'));
    }

    private function dispatch(string $eventName, array $data): void
    {
        if ($eventName === 'sales_model_service_quote_submit_success') {
            $observer = ObjectManager::getInstance()->create(StoreContextAndPublishPurchase::class);
            $observer->execute(new Observer(['event' => new Event($data)]));
            return;
        }

        ObjectManager::getInstance()->get(ManagerInterface::class)->dispatch($eventName, $data);
    }
}
