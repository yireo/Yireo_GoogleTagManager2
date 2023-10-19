<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Processor;

use AdPage\GTM\Api\Data\ProcessorInterface;

class Checkout implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        return $data;
    }
}
