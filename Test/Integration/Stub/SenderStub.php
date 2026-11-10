<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Stub;

use Yireo\GoogleTagManager2\ServerSide\Sender;
use Yireo\GoogleTagManager2\ServerSide\SendResult;

class SenderStub extends Sender
{
    public array $requests = [];
    public int $status = 204;

    public function send(int $storeId, string $payload, ?bool $validation = null): SendResult
    {
        $this->requests[] = ['store_id' => $storeId, 'payload' => $payload];
        return new SendResult($this->status >= 200 && $this->status < 300, $this->status, '');
    }
}
