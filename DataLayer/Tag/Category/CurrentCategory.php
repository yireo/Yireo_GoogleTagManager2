<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag\Category;

use Magento\Framework\Exception\NoSuchEntityException;
use Tagging\GTM\DataLayer\Mapper\CategoryDataMapper;
use Tagging\GTM\Api\Data\MergeTagInterface;
use Tagging\GTM\Util\GetCurrentCategory;

class CurrentCategory implements MergeTagInterface
{
    private GetCurrentCategory $getCurrentCategory;
    private CategoryDataMapper $categoryDataMapper;

    /**
     * @param GetCurrentCategory $getCurrentCategory
     * @param CategoryDataMapper $categoryDataMapper
     */
    public function __construct(
        GetCurrentCategory $getCurrentCategory,
        CategoryDataMapper $categoryDataMapper
    ) {
        $this->getCurrentCategory = $getCurrentCategory;
        $this->categoryDataMapper = $categoryDataMapper;
    }

    /**
     * @return string[]
     * @throws NoSuchEntityException
     */
    public function merge(): array
    {
        $currentCategory = $this->getCurrentCategory->get();
        return $this->categoryDataMapper->mapByCategory($currentCategory);
    }
}
