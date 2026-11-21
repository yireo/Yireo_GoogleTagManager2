<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Consent;

interface ConsentResolverInterface
{
    public function isActive(?int $storeId = null): bool;

    /**
     * @return array{analytics: bool, marketing: bool, preferences: bool}|null
     */
    public function getConsent(?int $storeId = null): ?array;
}
