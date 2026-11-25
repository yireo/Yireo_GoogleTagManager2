<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide\Context;

use InvalidArgumentException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextRepository;

class ContextRepositoryTest extends TestCase
{
    public function testCleanupBindsTheInterval(): void
    {
        $where = null;
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('delete')->willReturnCallback(function ($table, $condition) use (&$where) {
            $where = $condition;
            return 0;
        });

        $this->createRepository($connection)->cleanup(0);

        $this->assertSame(['created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? DAY)' => 1], $where);
    }

    public function testLoadRejectsUnknownFields(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->never())->method('select');

        $load = new ReflectionMethod(ContextRepository::class, 'load');
        $this->expectException(InvalidArgumentException::class);
        $load->invoke($this->createRepository($connection), 'quote_id = 1 OR entity_id', 1);
    }

    private function createRepository(AdapterInterface $connection): ContextRepository
    {
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($connection);
        $resourceConnection->method('getTableName')->willReturnArgument(0);

        return new ContextRepository($resourceConnection, new Json());
    }
}
