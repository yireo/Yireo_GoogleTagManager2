<?php declare(strict_types=1);

namespace AdPage\GTM\Api\Data;

use Magento\Framework\View\Element\Block\ArgumentInterface;

interface ProcessorInterface extends ArgumentInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function process(array $data): array;
}
