<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag\Category;

use AdPage\GTM\Api\Data\TagInterface;

class CategorySize implements TagInterface
{
    private int $size = 0;

    /**
     * @param int $size
     * @return void
     */
    public function setSize(int $size = 0)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function get(): int
    {
        return $this->size;
    }
}
