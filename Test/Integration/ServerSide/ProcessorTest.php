<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\ObjectManager as TestObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Cron\Outbox;
use Yireo\GoogleTagManager2\ServerSide\EventPublisher;
use Yireo\GoogleTagManager2\ServerSide\Processor;
use Yireo\GoogleTagManager2\ServerSide\Sender;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;
use Yireo\GoogleTagManager2\Test\Integration\Stub\SenderStub;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProcessorTest extends TestCase
{
    use ServerSideConfig;

    private SenderStub $sender;

    protected function setUp(): void
    {
        /** @var TestObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();
        $this->sender = $objectManager->create(SenderStub::class);
        $objectManager->addSharedInstance($this->sender, Sender::class);
        $objectManager->removeSharedInstance(Processor::class);
    }

    protected function tearDown(): void
    {
        /** @var TestObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();
        $objectManager->removeSharedInstance(Sender::class);
        $objectManager->removeSharedInstance(Processor::class);
    }

    public function testCronSendsPendingEvents(): void
    {
        $this->configureServerSide(['serverside_tracking/max_attempts' => '2']);
        $this->clearOutbox();
        $this->publish('add_to_cart:1');

        ObjectManager::getInstance()->create(Outbox::class)->processPending();

        $rows = $this->getOutboxRows();
        $this->assertCount(1, $this->sender->requests);
        $this->assertSame('sent', $rows[0]['status']);
        $this->assertSame('1', (string)$rows[0]['attempts']);
        $this->assertNotEmpty($rows[0]['sent_at']);
    }

    public function testFailedEventsAreRetriedUntilMaxAttempts(): void
    {
        $this->configureServerSide(['serverside_tracking/max_attempts' => '2']);
        $this->clearOutbox();
        $this->sender->status = 500;
        $this->publish('add_to_cart:2');

        $processor = ObjectManager::getInstance()->create(Processor::class);
        $this->assertSame(0, $processor->processPending());
        $this->assertSame('failed', $this->getOutboxRows()[0]['status']);

        $processor->retryFailed();
        $processor->retryFailed();

        $rows = $this->getOutboxRows();
        $this->assertCount(2, $this->sender->requests);
        $this->assertSame('failed', $rows[0]['status']);
        $this->assertSame('2', (string)$rows[0]['attempts']);
        $this->assertStringContainsString('HTTP 500', (string)$rows[0]['last_error']);

        $this->sender->status = 204;
        $processor->retryFailed();
        $this->assertCount(2, $this->sender->requests);
    }

    public function testCronSkipsPendingInQueueMode(): void
    {
        $this->configureServerSide(['serverside_tracking/dispatch_mode' => 'queue']);
        $this->clearOutbox();
        $this->publish('add_to_cart:3');

        ObjectManager::getInstance()->create(Outbox::class)->processPending();
        $this->assertCount(0, $this->sender->requests);
        $this->assertSame('pending', $this->getOutboxRows()[0]['status']);
    }

    private function publish(string $dedupKey): void
    {
        $eventPublisher = ObjectManager::getInstance()->create(EventPublisher::class);
        $outboxId = $eventPublisher->publish(
            ['event' => 'add_to_cart', 'ecommerce' => ['currency' => 'USD', 'value' => 10]],
            1,
            $dedupKey,
            new Context('1.2')
        );
        $this->assertNotNull($outboxId);
    }
}
