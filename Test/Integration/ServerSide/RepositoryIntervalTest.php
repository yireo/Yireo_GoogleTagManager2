<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextRepository;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RepositoryIntervalTest extends TestCase
{
    public function testOutboxIntervalsAreApplied(): void
    {
        $repository = Bootstrap::getObjectManager()->get(OutboxRepository::class);
        $oldId = $repository->add(1, 'purchase', 'interval-test:old', '{}');
        $newId = $repository->add(1, 'purchase', 'interval-test:new', '{}');
        $this->age(OutboxRepository::TABLE, $oldId, 'INTERVAL 2 DAY');

        $ids = $repository->getIdsByStatus(OutboxRepository::STATUS_PENDING, 5, 1000, 60);
        $this->assertContains($oldId, $ids);
        $this->assertNotContains($newId, $ids);

        $repository->claim($oldId, 5);
        $repository->claim($newId, 5);
        $this->age(OutboxRepository::TABLE, $oldId, 'INTERVAL 2 DAY');
        $this->assertGreaterThanOrEqual(1, $repository->releaseStale(60));
        $this->assertSame(OutboxRepository::STATUS_FAILED, $repository->getById($oldId)['status']);
        $this->assertSame(OutboxRepository::STATUS_PROCESSING, $repository->getById($newId)['status']);

        $this->age(OutboxRepository::TABLE, $oldId, 'INTERVAL 2 DAY');
        $repository->cleanup(1);
        $this->assertNull($repository->getById($oldId));
        $this->assertNotNull($repository->getById($newId));
    }

    public function testContextCleanupIntervalIsApplied(): void
    {
        $repository = Bootstrap::getObjectManager()->get(ContextRepository::class);
        $repository->save(998001, 998002, new Context('1.2', null, null, null));
        $repository->save(998003, 998004, new Context('3.4', null, null, null));

        $connection = $this->getConnection();
        $table = $connection->getTableName(ContextRepository::TABLE);
        $connection->update(
            $table,
            ['created_at' => new Expression('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY)')],
            ['quote_id = ?' => 998001]
        );

        $repository->cleanup(1);
        $this->assertNull($repository->getByOrderId(998002));
        $this->assertNotNull($repository->getByOrderId(998004));
    }

    private function age(string $table, int $id, string $interval): void
    {
        $connection = $this->getConnection();
        $connection->update(
            $connection->getTableName($table),
            [
                'created_at' => new Expression('DATE_SUB(CURRENT_TIMESTAMP, ' . $interval . ')'),
                'updated_at' => new Expression('DATE_SUB(CURRENT_TIMESTAMP, ' . $interval . ')'),
            ],
            ['entity_id = ?' => $id]
        );
    }

    private function getConnection()
    {
        return Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
    }
}
