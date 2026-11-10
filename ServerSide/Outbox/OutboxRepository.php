<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Outbox;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Sql\Expression;

class OutboxRepository
{
    public const TABLE = 'yireo_gtm_serverside_event';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    private ResourceConnection $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function add(int $storeId, string $eventName, string $dedupKey, string $payload): ?int
    {
        $connection = $this->getConnection();
        $affectedRows = $connection->insertArray(
            $this->getTable(),
            ['store_id', 'event_name', 'dedup_key', 'payload', 'status'],
            [[$storeId, $eventName, $dedupKey, $payload, self::STATUS_PENDING]],
            AdapterInterface::INSERT_IGNORE
        );

        if ($affectedRows !== 1) {
            return null;
        }

        return (int)$connection->lastInsertId($this->getTable());
    }

    public function exists(string $dedupKey): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable(), ['entity_id'])
            ->where('dedup_key = ?', $dedupKey);

        return (bool)$connection->fetchOne($select);
    }

    public function getById(int $id): ?array
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable())->where('entity_id = ?', $id);
        $row = $connection->fetchRow($select);

        return empty($row) ? null : $row;
    }

    public function getIdsByStatus(string $status, int $maxAttempts, int $limit, int $olderThanMinutes = 0): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable(), ['entity_id'])
            ->where('status = ?', $status)
            ->where('attempts < ?', $maxAttempts)
            ->order('entity_id ASC')
            ->limit($limit);

        if ($olderThanMinutes > 0) {
            $select->where('created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . $olderThanMinutes . ' MINUTE)');
        }

        return array_map('intval', $connection->fetchCol($select));
    }

    public function claim(int $id, int $maxAttempts): bool
    {
        return $this->getConnection()->update(
            $this->getTable(),
            ['status' => self::STATUS_PROCESSING],
            [
                'entity_id = ?' => $id,
                'status IN (?)' => [self::STATUS_PENDING, self::STATUS_FAILED],
                'attempts < ?' => $maxAttempts,
            ]
        ) === 1;
    }

    public function markSent(int $id): void
    {
        $this->getConnection()->update(
            $this->getTable(),
            [
                'status' => self::STATUS_SENT,
                'attempts' => new Expression('attempts + 1'),
                'last_error' => null,
                'sent_at' => new Expression('CURRENT_TIMESTAMP'),
            ],
            ['entity_id = ?' => $id]
        );
    }

    public function markFailed(int $id, string $error): void
    {
        $this->getConnection()->update(
            $this->getTable(),
            [
                'status' => self::STATUS_FAILED,
                'attempts' => new Expression('attempts + 1'),
                'last_error' => mb_substr($error, 0, 2000),
            ],
            ['entity_id = ?' => $id]
        );
    }

    public function releaseStale(int $minutes): int
    {
        return $this->getConnection()->update(
            $this->getTable(),
            [
                'status' => self::STATUS_FAILED,
                'attempts' => new Expression('attempts + 1'),
                'last_error' => 'Processing timed out',
            ],
            [
                'status = ?' => self::STATUS_PROCESSING,
                'updated_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . max(1, $minutes) . ' MINUTE)',
            ]
        );
    }

    public function cleanup(int $days): int
    {
        return $this->getConnection()->delete(
            $this->getTable(),
            [
                'status IN (?)' => [self::STATUS_SENT, self::STATUS_FAILED],
                'created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . max(1, $days) . ' DAY)',
            ]
        );
    }

    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    private function getTable(): string
    {
        return $this->resourceConnection->getTableName(self::TABLE);
    }
}
