<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ObjectManager;
use RuntimeException;

trait CreateCategory
{
    public function createCategory(
        int $id,
        int $parentId = 2,
        array $data = []
    ): CategoryInterface {
        if ($id < 3) {
            throw new RuntimeException('Category ID should not be lower than 3');
        }

        $categoryFactory = ObjectManager::getInstance()->get(CategoryInterfaceFactory::class);
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);

        /** @var $category Category */
        $category = $categoryFactory->create();
        $category->setId($id)
            ->setName(isset($data['name']) ? $data['name'] : 'Category ' . $id)
            ->setParentId($parentId)
            ->setPath('1/' . $parentId . '/' . $id)
            ->setUrlKey('category' . $id)
            ->setLevel(2)
            ->setIsActive(true)
            ->setPosition(1)
            ->addData($data);
        $category->isObjectNew(true);
        $category->save();

        return $categoryRepository->save($category);
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
