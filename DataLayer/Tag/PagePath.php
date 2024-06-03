<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag;

use Magento\Framework\UrlInterface;
use Tagging\GTM\Api\Data\TagInterface;

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
