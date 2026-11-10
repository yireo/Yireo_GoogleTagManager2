<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Queue;

use Yireo\GoogleTagManager2\ServerSide\Processor;

class Consumer
{
    private Processor $processor;

    public function __construct(
        Processor $processor
    ) {
        $this->processor = $processor;
    }

    public function process(string $outboxId): void
    {
        $this->processor->processById((int)$outboxId);
    }
}
