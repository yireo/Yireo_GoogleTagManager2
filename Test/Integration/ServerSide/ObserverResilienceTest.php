<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yireo\GoogleTagManager2\ServerSide\Observer\PublishPurchaseOnInvoicePay;
use Yireo\GoogleTagManager2\ServerSide\Observer\PublishPurchaseOnOrderState;
use Yireo\GoogleTagManager2\ServerSide\Observer\PublishRefund;
use Yireo\GoogleTagManager2\ServerSide\Observer\StoreContextAndPublishPurchase;
use Yireo\GoogleTagManager2\ServerSide\OrderEventPublisher;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class ObserverResilienceTest extends TestCase
{
    use ServerSideConfig;

    public function testObserversDoNotThrow(): void
    {
        $this->configureServerSide([
            'serverside_tracking/purchase_trigger' => 'order_state',
            'settings/order_states_for_purchase_event' => 'complete',
        ]);

        $order = ObjectManager::getInstance()->create(Order::class);
        $order->setState(Order::STATE_COMPLETE);
        $order->setStoreId(1);

        $invoice = ObjectManager::getInstance()->create(Invoice::class);
        $invoice->setOrder($order);

        $creditmemo = ObjectManager::getInstance()->create(Creditmemo::class);
        $creditmemo->setId(1);
        $creditmemo->setOrder($order);

        $this->execute(StoreContextAndPublishPurchase::class, ['order' => $order]);
        $this->execute(PublishPurchaseOnOrderState::class, ['order' => $order]);
        $this->configureServerSide(['serverside_tracking/purchase_trigger' => 'invoice_pay']);
        $this->execute(PublishPurchaseOnInvoicePay::class, ['invoice' => $invoice]);
        $this->execute(PublishRefund::class, ['creditmemo' => $creditmemo]);
    }

    private function execute(string $observerClass, array $data): void
    {
        $orderEventPublisher = $this->createMock(OrderEventPublisher::class);
        $exception = new RuntimeException('Database is gone');
        $orderEventPublisher->method('storeContext')->willThrowException($exception);
        $orderEventPublisher->method('publishPurchase')->willThrowException($exception);
        $orderEventPublisher->method('publishRefund')->willThrowException($exception);

        /** @var ObserverInterface $observer */
        $observer = ObjectManager::getInstance()->create($observerClass, [
            'orderEventPublisher' => $orderEventPublisher,
        ]);

        $observer->execute(new Observer(['event' => new Event($data)]));
        $this->addToAssertionCount(1);
    }
}
