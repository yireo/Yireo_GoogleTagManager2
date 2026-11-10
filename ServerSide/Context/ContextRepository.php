<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Context;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

class ContextRepository
{
    public const TABLE = 'yireo_gtm_serverside_context';

    private ResourceConnection $resourceConnection;
    private Json $json;

    public function __construct(
        ResourceConnection $resourceConnection,
        Json $json
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->json = $json;
    }

    public function save(int $quoteId, ?int $orderId, Context $context): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(
            $this->resourceConnection->getTableName(self::TABLE),
            [
                'quote_id' => $quoteId,
                'order_id' => $orderId,
                'client_id' => $context->getClientId(),
                'session_id' => $context->getSessionId(),
                'user_id' => $context->getUserId(),
                'consent' => $context->getConsent() !== null ? $this->json->serialize($context->getConsent()) : null,
            ],
            ['order_id', 'client_id', 'session_id', 'user_id', 'consent']
        );
    }

    public function getByOrderId(int $orderId): ?Context
    {
        return $this->load('order_id', $orderId);
    }

    public function getByQuoteId(int $quoteId): ?Context
    {
        return $this->load('quote_id', $quoteId);
    }

    public function cleanup(int $days): int
    {
        $connection = $this->resourceConnection->getConnection();
        return $connection->delete(
            $this->resourceConnection->getTableName(self::TABLE),
            ['created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . max(1, $days) . ' DAY)']
        );
    }

    private function load(string $field, int $value): ?Context
    {
        if ($value <= 0) {
            return null;
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName(self::TABLE))
            ->where($field . ' = ?', $value)
            ->order('entity_id DESC')
            ->limit(1);
        $row = $connection->fetchRow($select);
        if (empty($row)) {
            return null;
        }

        $consent = !empty($row['consent']) ? $this->json->unserialize($row['consent']) : null;

        return new Context(
            (string)$row['client_id'],
            $row['session_id'] !== null ? (string)$row['session_id'] : null,
            $row['user_id'] !== null ? (string)$row['user_id'] : null,
            is_array($consent) ? $consent : null
        );
    }
}
