<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Logger;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yireo\GoogleTagManager2\Logger\PiiRedactor;

class PiiRedactorTest extends TestCase
{
    public function testRedactsPiiKeysRecursively(): void
    {
        $data = [
            'event' => 'purchase',
            'client_id' => '123.456',
            'user_id' => 42,
            'session_id' => 'abc',
            'email' => 'john@example.com',
            'customer_email' => 'john@example.com',
            'sha256_email_address' => 'hash',
            'customer' => ['firstname' => 'John'],
            'customerId' => 42,
            'customer_customer_firstname' => 'John',
            'user_data' => ['address' => ['city' => 'Amsterdam']],
            'api_secret' => 's3cr3t',
            'ecommerce' => [
                'transaction_id' => '100000001',
                'items' => [['item_id' => 'SKU1', 'Email' => 'john@example.com']],
                'nested' => ['SHA256_phone_number' => 'hash', 'Client_Id' => 'x'],
            ],
            'object' => new stdClass(),
        ];

        $expected = [
            'event' => 'purchase',
            'client_id' => PiiRedactor::REDACTED,
            'user_id' => PiiRedactor::REDACTED,
            'session_id' => PiiRedactor::REDACTED,
            'email' => PiiRedactor::REDACTED,
            'customer_email' => PiiRedactor::REDACTED,
            'sha256_email_address' => PiiRedactor::REDACTED,
            'customer' => PiiRedactor::REDACTED,
            'customerId' => PiiRedactor::REDACTED,
            'customer_customer_firstname' => PiiRedactor::REDACTED,
            'user_data' => PiiRedactor::REDACTED,
            'api_secret' => PiiRedactor::REDACTED,
            'ecommerce' => [
                'transaction_id' => '100000001',
                'items' => [['item_id' => 'SKU1', 'Email' => PiiRedactor::REDACTED]],
                'nested' => ['SHA256_phone_number' => PiiRedactor::REDACTED, 'Client_Id' => PiiRedactor::REDACTED],
            ],
            'object' => '(object) stdClass',
        ];

        $this->assertSame($expected, (new PiiRedactor())->redact($data));
    }

    public function testKeepsScalarsAndNumericKeys(): void
    {
        $redactor = new PiiRedactor();
        $this->assertSame('timeout', $redactor->redact('timeout'));
        $this->assertSame(null, $redactor->redact(null));
        $this->assertSame([1 => 'a', 'items' => ['b']], $redactor->redact([1 => 'a', 'items' => ['b']]));
    }
}
