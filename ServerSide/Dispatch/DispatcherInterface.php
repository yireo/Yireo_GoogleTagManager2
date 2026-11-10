<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Dispatch;

interface DispatcherInterface
{
    public function dispatch(int $outboxId): void;
}
