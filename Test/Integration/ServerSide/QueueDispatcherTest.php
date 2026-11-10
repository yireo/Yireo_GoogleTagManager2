<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Dispatch\QueueDispatcher;
use Yireo\GoogleTagManager2\ServerSide\EventPublisher;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\ServerSideConfig;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class QueueDispatcherTest extends TestCase
{
    use ServerSideConfig;

    public function testPublishToQueue(): void
    {
        $this->configureServerSide(['serverside_tracking/dispatch_mode' => 'queue']);
        $this->clearOutbox();

        $outboxId = ObjectManager::getInstance()->create(EventPublisher::class)->publish(
            ['event' => 'login', 'method' => 'Standard'],
            1,
            'login:queue-test',
            new Context('1.2')
        );
        $this->assertNotNull($outboxId);

        $resourceConnection = ObjectManager::getInstance()->get(ResourceConnection::class);
        $connection = $resourceConnection->getConnection();
        $select = $connection->select()
            ->from($resourceConnection->getTableName('queue_message'), ['body'])
            ->where('topic_name = ?', QueueDispatcher::TOPIC)
            ->order('id DESC')
            ->limit(1);

        $this->assertSame((string)$outboxId, json_decode((string)$connection->fetchOne($select), true));
    }
}
