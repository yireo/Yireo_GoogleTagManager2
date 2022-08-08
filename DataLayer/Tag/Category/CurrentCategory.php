<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Yireo\GoogleTagManager2\DataLayer\Tag\MergeTagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CategoryDataMapper;

class CurrentCategory implements MergeTagInterface
{
    private GetCurrentCategory $getCurrentCategory;
    private CategoryDataMapper $categoryDataMapper;

    public function __construct(
        GetCurrentCategory $getCurrentCategory,
        CategoryDataMapper $categoryDataMapper
    ) {
        $this->getCurrentCategory = $getCurrentCategory;
        $this->categoryDataMapper = $categoryDataMapper;
    }

    public function mergeData(): array
    {
        $category = $this->getCurrentCategory->get();
        return $this->categoryDataMapper->mapByCategory($category, 'category');
    }
}
