<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Tagging\GTM\Api\Data\TagInterface;
use Tagging\GTM\Logger\Debugger;
class CurrencyCode implements TagInterface
{
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;
    private Debugger $debugger;

    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Debugger $debugger
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->debugger = $debugger;
    }

    public function get(): string
    {
        try {
            return $this->storeManager->getStore()->getCurrentCurrencyCode() ?: ''; // @phpstan-ignore-line
        } catch (NoSuchEntityException $e) {
            $this->debugger->debug('Cannot retrieve currency code for current store. ' . $e->getMessage());
            $this->logger->warning('Cannot retrieve currency code for current store. ' . $e->getMessage());
            return '';
        }
    }
}
