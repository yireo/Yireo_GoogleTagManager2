<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;

class CurrentCategoryName implements TagInterface
{
    private GetCurrentCategory $getCurrentCategory;

    public function __construct(
        GetCurrentCategory $getCurrentCategory,
    ) {
        $this->getCurrentCategory = $getCurrentCategory;
    }

    public function get(): string
    {
        return $this->getCurrentCategory->get()->getName();
    }
}
