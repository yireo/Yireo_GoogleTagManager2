<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;

trait GetCategory
{
    /**
     * @param string $categoryName
     * @return CategoryInterface
     */
    public function getCategoryByName(string $categoryName): CategoryInterface
    {
        $getCategoryByName = ObjectManager::getInstance()->get(GetCategoryByName::class);
        return $getCategoryByName->execute($categoryName);
    }
}
