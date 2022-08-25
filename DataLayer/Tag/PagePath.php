<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\UrlInterface;

class PagePath implements AddTagInterface
{
    private UrlInterface $url;

    public function __construct(
        UrlInterface $url
    ) {
        $this->url = $url;
    }

    public function addData(): string
    {
        return $this->url->getCurrentUrl();
    }
}
