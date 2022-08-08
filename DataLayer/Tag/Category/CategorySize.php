<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Category;

use Yireo\GoogleTagManager2\DataLayer\Tag\AddTagInterface;

class CategorySize implements AddTagInterface
{
    private $size = 0;

    public function setSize(int $size = 0)
    {
        $this->size = $size;
    }

    public function addData(): int
    {
        return $this->size;
    }
}
