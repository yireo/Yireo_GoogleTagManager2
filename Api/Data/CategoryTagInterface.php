<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api\Data;

use Magento\Catalog\Api\Data\CategoryInterface;

interface CategoryTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function setCategory(CategoryInterface $category);
}
