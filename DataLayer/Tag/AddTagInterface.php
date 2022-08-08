<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

interface AddTagInterface extends TagInterface
{
    /**
     * @return mixed
     */
    public function addData();
}
