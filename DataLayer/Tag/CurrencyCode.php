<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use AdPage\GTM\Api\Data\TagInterface;

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
            return $this->storeManager->getStore()->getCurrentCurrencyCode() ?: ''; // @phpstan-ignore-line
        } catch (NoSuchEntityException $e) {
            $this->logger->warning('Cannot retrieve currency code for current store. ' . $e->getMessage());
            return '';
        }
    }
}
