<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;

class CategorySize implements TagInterface
{
    private $size = 0;

    public function setSize(int $size = 0)
    {
        $this->size = $size;
    }

    public function get(): int
    {
        return $this->size;
    }
}
