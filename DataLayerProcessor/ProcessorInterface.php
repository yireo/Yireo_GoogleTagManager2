<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayerProcessor;

use Magento\Framework\View\Element\Block\ArgumentInterface;

interface ProcessorInterface extends ArgumentInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function process(array $data): array;
}
