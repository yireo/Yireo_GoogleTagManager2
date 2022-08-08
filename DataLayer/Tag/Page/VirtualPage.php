<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Page;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\AddTagInterface;

class VirtualPage implements AddTagInterface
{
    private StoreManagerInterface $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function addData(): string
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $url = $store->getCurrentUrl();
        $urlData = parse_url($url);
        return isset($urlData['path']) ? rtrim($urlData['path'], '/') : '';
    }
}
