<?php

declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag\Store;

use Tagging\GTM\Api\Data\TagInterface;
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
        return [
            'code' => $this->storeManager->getStore()->getCode(),
            'name' => $this->storeManager->getStore()->getName(),
            'website_id' => $this->storeManager->getStore()->getWebsiteId(),
            'url' => $this->storeManager->getStore()->getCurrentUrl(),
        ];
    }
}
