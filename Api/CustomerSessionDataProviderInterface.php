<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Api;

interface CustomerSessionDataProviderInterface
{
    public function append(array $data);
    public function get(): array;
    public function clear();
}
