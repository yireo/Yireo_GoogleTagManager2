<?php

declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Store;

use AdPage\GTM\Api\Data\TagInterface;
use Magento\Store\Model\StoreManagerInterface;

class CurrentStore implements TagInterface
{
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
    ) {
        $this->storeManager = $storeManager;
    }

    public function get(): array
    {
        try {
            return [
                'code' => $this->storeManager->getStore()->getCode(),
                'name' => $this->storeManager->getStore()->getName(),
                'website_id' => $this->storeManager->getStore()->getWebsiteId(),
                'url' => $this->storeManager->getStore()->getCurrentUrl(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => null,
                'name' => null,
                'website_id' => null,
                'url' => null,
            ];
        }
    }
}
