<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Indexer\Model\IndexerFactory;

trait Reindex
{
    public function reindex(string $indexerId)
    {
        $objectManager = ObjectManager::getInstance();
        $indexerFactory = $objectManager->get(IndexerFactory::class);
        $indexer = $indexerFactory->create()->load($indexerId);
        $indexer->reindexAll();
    }

    public function reindexAll()
    {
        $objectManager = ObjectManager::getInstance();
        $config = $objectManager->get(ConfigInterface::class);
        $indexerFactory = $objectManager->get(IndexerFactory::class);
        foreach (array_keys($config->getIndexers()) as $indexerId) {
            $indexer = $indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
    }
}
