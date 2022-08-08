<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

interface MergeTagInterface extends TagInterface
{
    public function mergeData(): array;
}
