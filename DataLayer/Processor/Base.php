<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Processor;

use Yireo\GoogleTagManager2\Api\Data\ProcessorInterface;

class Base implements ProcessorInterface
{
    public function process(array $data): array
    {
        return $data;
    }
}
