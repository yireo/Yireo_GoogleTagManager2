<?php declare(strict_types=1);

namespace AdPage\GTM\Util;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Exception\NoSuchEntityException;

class GetCategoryPath
{
    /**
     * @var array
     */
    const ROOT_CATEGORY_IDS = [1, 2];

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Category constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param ?CategoryModel $category
     * @return string
     */
    public function getCategoryPath(?CategoryModel $category = null): string
    {
        if (!$category instanceof CategoryModel) {
            return "direct";
        }

        $categoryPath = $category->getPath();
        $categoryIdArray = explode('/', $categoryPath);
        $categoryNames = [];

        foreach ($categoryIdArray as $categoryId) {
            if (!in_array($categoryId, self::ROOT_CATEGORY_IDS)) {
                try {
                    $category = $this->categoryRepository->get($categoryId);
                } catch (NoSuchEntityException $e) {
                    continue;
                }
                $categoryNames[] = $category->getName();
            }
        }
        return implode('/', $categoryNames);
    }
}
