<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CategoryDataMapper;
use Yireo\GoogleTagManager2\Api\Data\MergeTagInterface;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;

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
