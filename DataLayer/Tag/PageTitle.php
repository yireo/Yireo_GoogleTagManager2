<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag;

use Magento\Framework\View\Page\Title;
use Tagging\GTM\Api\Data\TagInterface;

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
