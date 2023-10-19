<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Page;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use AdPage\GTM\Api\Data\TagInterface;

class VirtualPage implements TagInterface
{
    private StoreManagerInterface $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function get(): string
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $url = $store->getCurrentUrl();
        $urlData = parse_url($url);
        return isset($urlData['path']) ? rtrim($urlData['path'], '/') : '';
    }
}
