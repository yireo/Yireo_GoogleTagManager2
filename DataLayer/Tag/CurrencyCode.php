<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

class CurrencyCode implements TagInterface
{
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function get(): string
    {
        try {
            $store = $this->storeManager->getStore();
            /** @var Store $store */
            return $store->getCurrentCurrencyCode() ?: '';
        } catch (NoSuchEntityException $e) {
            $this->logger->warning('Cannot retrieve currency code for current store. ' . $e->getMessage());
            return '';
        }
    }
}
