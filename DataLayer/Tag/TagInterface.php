<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\View\Element\Block\ArgumentInterface;

interface TagInterface extends ArgumentInterface
{
    /**
     * @return mixed
     */
    public function get();
}
