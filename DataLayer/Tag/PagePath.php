<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\UrlInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

class PagePath implements TagInterface
{
    private UrlInterface $url;

    public function __construct(
        UrlInterface $url
    ) {
        $this->url = $url;
    }

    public function get(): string
    {
        return $this->url->getCurrentUrl();
    }
}
