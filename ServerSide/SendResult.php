<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

class SendResult
{
    private bool $success;
    private int $status;
    private string $body;

    public function __construct(bool $success, int $status, string $body)
    {
        $this->success = $success;
        $this->status = $status;
        $this->body = $body;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
