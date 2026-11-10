<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Context;

class Context
{
    private string $clientId;
    private ?string $sessionId;
    private ?string $userId;
    private ?array $consent;

    public function __construct(
        string $clientId,
        ?string $sessionId = null,
        ?string $userId = null,
        ?array $consent = null
    ) {
        $this->clientId = $clientId;
        $this->sessionId = $sessionId;
        $this->userId = $userId;
        $this->consent = $consent;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getConsent(): ?array
    {
        return $this->consent;
    }

    public function hasConsent(string $type): bool
    {
        return is_array($this->consent) && ($this->consent[$type] ?? false) === true;
    }
}
