<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Processor;

class Product implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        return $data;
    }
}
