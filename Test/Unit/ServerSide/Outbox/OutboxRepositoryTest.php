<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide\Outbox;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;

class OutboxRepositoryTest extends TestCase
{
    public function testReleaseStaleBindsTheInterval(): void
    {
        $where = null;
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('update')->willReturnCallback(function ($table, $bind, $condition) use (&$where) {
            $where = $condition;
            return 0;
        });

        $this->createRepository($connection)->releaseStale(0);

        $this->assertSame(1, $where['updated_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? MINUTE)'] ?? null);
        $this->assertNoLiteralInterval($where);
    }

    public function testCleanupBindsTheInterval(): void
    {
        $where = null;
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('delete')->willReturnCallback(function ($table, $condition) use (&$where) {
            $where = $condition;
            return 0;
        });

        $this->createRepository($connection)->cleanup(30);

        $this->assertSame(30, $where['created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? DAY)'] ?? null);
        $this->assertNoLiteralInterval($where);
    }

    public function testGetIdsByStatusBindsTheInterval(): void
    {
        $conditions = [];
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('order')->willReturnSelf();
        $select->method('limit')->willReturnSelf();
        $select->method('where')->willReturnCallback(function ($condition, $value = null) use (&$conditions, $select) {
            $conditions[$condition] = $value;
            return $select;
        });

        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchCol')->willReturn(['3']);

        $ids = $this->createRepository($connection)->getIdsByStatus('pending', 5, 10, 15);

        $this->assertSame([3], $ids);
        $this->assertSame(15, $conditions['created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? MINUTE)'] ?? null);
        $this->assertNoLiteralInterval($conditions);
    }

    private function assertNoLiteralInterval(array $conditions): void
    {
        foreach ($conditions as $key => $value) {
            $this->assertDoesNotMatchRegularExpression('/INTERVAL\s+\d/', is_string($key) ? $key : (string)$value);
        }
    }

    private function createRepository(AdapterInterface $connection): OutboxRepository
    {
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($connection);
        $resourceConnection->method('getTableName')->willReturnArgument(0);

        return new OutboxRepository($resourceConnection);
    }
}
