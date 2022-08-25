<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Framework\App\ObjectManager;

trait CreateCategory
{
    public function createCategory(
        int $id,
        int $parentId = 2
    ): CategoryInterface
    {
        $categoryFactory = ObjectManager::getInstance()->get(CategoryInterfaceFactory::class);

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $categoryFactory->create();
        $category->isObjectNew(true);
        $category->setId($id)
            ->setName('Category '.$id)
            ->setParentId($parentId)
            ->setPath('1/'.$parentId.'/'.$id)
            ->setUrlKey('category'.$id)
            ->setLevel(2)
            ->setIsActive(true)
            ->save();

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
