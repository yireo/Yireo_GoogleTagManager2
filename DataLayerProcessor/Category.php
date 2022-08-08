<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayerProcessor;

class Category implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        return $data;
    }
}
