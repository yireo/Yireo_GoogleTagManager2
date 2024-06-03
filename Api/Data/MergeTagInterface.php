<?php declare(strict_types=1);

namespace Tagging\GTM\Api\Data;

use Magento\Framework\View\Element\Block\ArgumentInterface;

interface MergeTagInterface extends ArgumentInterface
{
    /**
     * @return array
     */
    public function merge(): array;
}
