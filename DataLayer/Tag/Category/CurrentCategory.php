<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;

class CurrentCategory implements ArgumentInterface
{
    private GetCurrentCategory $getCurrentCategory;

    public function __construct(
        GetCurrentCategory $getCurrentCategory,
    ) {
        $this->getCurrentCategory = $getCurrentCategory;
    }

    public function getId(): int
    {
        return (int)$this->getCurrentCategory->get()->getId();
    }

    public function getName(): string
    {
        return (string)$this->getCurrentCategory->get()->getName();
    }
}
