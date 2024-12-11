<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

trait CreateCategory
{
    public function createCategory(
        int $id,
        int $parentId = 2,
        array $data = []
    ): CategoryInterface {
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        try {
            if ($category = $categoryRepository->get($id)) {
                return $category;
            }
        } catch (NoSuchEntityException $e) {
        }

        /** @var $category Category */
        $categoryFactory = ObjectManager::getInstance()->get(CategoryInterfaceFactory::class);
        $category = $categoryFactory->create();
        $category
            ->setId($id)
            ->setName(isset($data['name']) ? $data['name'] : 'Category '.$id)
            ->setUrlKey('category'.$id)
            ->setUrlPath('category'.$id)
            ->setLevel(2)
            ->setPath(Category::TREE_ROOT_ID.'/'.$parentId.'/'.$id)
            ->setParentId($parentId)
            ->setStoreId(1)
            ->setIsActive(true)
            ->setIncludeInMenu(true)
            ->setPosition(1)
            ->addData($data);

        $category->isObjectNew(true);
        $category->save();

        return $category;
    }

    public function createCategories($numberOfCategories = 1): array
    {
        $categories = [];
        for ($i = 3; $i < $numberOfCategories + 3; $i++) {
            $categories[] = $this->createCategory($i);
        }

        return $categories;
    }
}
