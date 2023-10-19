<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Processor;

use AdPage\GTM\Api\Data\ProcessorInterface;

class SuccessPage implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        return $data;
    }
}
