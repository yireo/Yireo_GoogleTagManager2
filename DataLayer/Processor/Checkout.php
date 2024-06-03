<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Processor;

use Tagging\GTM\Api\Data\ProcessorInterface;

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
