<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Stub;

use Yireo\GoogleTagManager2\ServerSide\Consent\ConsentResolverInterface;

class ConsentResolverStub implements ConsentResolverInterface
{
    public bool $active = true;
    public ?array $consent = null;

    public function isActive(?int $storeId = null): bool
    {
        return $this->active;
    }

    public function getConsent(?int $storeId = null): ?array
    {
        return $this->consent;
    }
}
