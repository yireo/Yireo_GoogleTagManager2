<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\ServerSide;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    public function testTablesExist(): void
    {
        $resourceConnection = ObjectManager::getInstance()->get(ResourceConnection::class);
        $connection = $resourceConnection->getConnection();
        foreach (['yireo_gtm_serverside_context', 'yireo_gtm_serverside_event'] as $table) {
            $this->assertTrue($connection->isTableExists($resourceConnection->getTableName($table)), $table);
        }
    }
}
