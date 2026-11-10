<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class RefundTest extends TestCase
{
    use ServerSideConfig;

    /**
     * @magentoDataFixture Magento/Sales/_files/creditmemo_for_get.php
     */
    public function testRefundOnCreditmemoSave(): void
    {
        $this->configureServerSide();
        $this->clearOutbox();

        $creditmemo = ObjectManager::getInstance()->create(Collection::class)->getFirstItem();
        $this->assertNotEmpty($creditmemo->getId());

        $eventManager = ObjectManager::getInstance()->get(ManagerInterface::class);
        $eventManager->dispatch('sales_order_creditmemo_save_after', ['creditmemo' => $creditmemo]);
        $eventManager->dispatch('sales_order_creditmemo_save_after', ['creditmemo' => $creditmemo]);

        $rows = $this->getOutboxRows('refund');
        $this->assertCount(1, $rows);
        $this->assertSame('refund:' . $creditmemo->getId(), $rows[0]['dedup_key']);

        $payload = json_decode($rows[0]['payload'], true);
        $params = $payload['events'][0]['params'];
        $this->assertSame('refund', $payload['events'][0]['name']);
        $this->assertSame($creditmemo->getOrder()->getIncrementId(), $params['transaction_id']);
        $this->assertEquals((float)$creditmemo->getGrandTotal(), $params['value']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+$/', $payload['client_id']);
    }
}
