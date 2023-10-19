<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag;

use Magento\Framework\View\Page\Title;
use AdPage\GTM\Api\Data\TagInterface;

class PageTitle implements TagInterface
{
    private Title $pageTitle;

    public function __construct(
        Title $pageTitle
    ) {
        $this->pageTitle = $pageTitle;
    }

    public function get(): string
    {
        return $this->pageTitle->get();
    }
}
