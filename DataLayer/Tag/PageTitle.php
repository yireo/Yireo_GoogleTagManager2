<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\View\Page\Title;

class PageTitle implements AddTagInterface
{
    private Title $pageTitle;

    public function __construct(
        Title $pageTitle
    ) {
        $this->pageTitle = $pageTitle;
    }

    public function addData(): string
    {
        return $this->pageTitle->get();
    }
}
