<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;

trait ServerSideConfig
{
    public function configureServerSide(array $values = []): void
    {
        $objectManager = ObjectManager::getInstance();
        $encryptor = $objectManager->get(EncryptorInterface::class);
        $values = array_merge([
            'serverside_tracking/enabled' => '1',
            'serverside_tracking/measurement_id' => 'G-TEST123',
            'serverside_tracking/api_secret' => $encryptor->encrypt('secret'),
            'serverside_tracking/purchase_trigger' => 'order_place',
            'serverside_tracking/dispatch_mode' => 'cron',
            'serverside_tracking/require_consent' => '0',
        ], $values);

        $scopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        foreach ($values as $path => $value) {
            $path = 'googletagmanager2/' . $path;
            $scopeConfig->setValue($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
            $scopeConfig->setValue($path, $value, ScopeInterface::SCOPE_STORE, 'default');
        }
    }

    public function getOutboxRows(?string $eventName = null): array
    {
        $resourceConnection = ObjectManager::getInstance()->get(ResourceConnection::class);
        $connection = $resourceConnection->getConnection();
        $select = $connection->select()->from($resourceConnection->getTableName(OutboxRepository::TABLE));
        if ($eventName !== null) {
            $select->where('event_name = ?', $eventName);
        }

        return $connection->fetchAll($select);
    }

    public function clearOutbox(): void
    {
        $resourceConnection = ObjectManager::getInstance()->get(ResourceConnection::class);
        $resourceConnection->getConnection()->delete($resourceConnection->getTableName(OutboxRepository::TABLE));
    }
}
