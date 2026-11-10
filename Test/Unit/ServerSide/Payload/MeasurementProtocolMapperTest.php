<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide\Payload;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Payload\MeasurementProtocolMapper;

class MeasurementProtocolMapperTest extends TestCase
{
    public function testMapPurchase(): void
    {
        $mapper = new MeasurementProtocolMapper();
        $context = new Context('123.456', '789', '42', ['analytics' => true, 'marketing' => false]);
        $body = $mapper->map([
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => '000000001',
                'currency' => 'USD',
                'value' => '12.50',
                'value_adjusted' => 10,
                'value_adjusted_currency' => 'USD',
                'payment_method' => 'checkmo',
                'coupon' => null,
                'items' => [
                    ['item_id' => 'SKU1', 'item_name' => 'Foo', 'price' => '6.25', 'quantity' => '2.0', 'details' => []],
                    ['foo' => 'bar'],
                ],
            ],
        ], $context, 1000);

        $this->assertSame('123.456', $body['client_id']);
        $this->assertSame('42', $body['user_id']);
        $this->assertSame(1000, $body['timestamp_micros']);
        $this->assertSame(['ad_user_data' => 'DENIED', 'ad_personalization' => 'DENIED'], $body['consent']);
        $this->assertSame('purchase', $body['events'][0]['name']);

        $params = $body['events'][0]['params'];
        $this->assertSame(12.5, $params['value']);
        $this->assertSame('000000001', $params['transaction_id']);
        $this->assertSame('789', $params['session_id']);
        $this->assertSame(1, $params['engagement_time_msec']);
        $this->assertArrayNotHasKey('value_adjusted', $params);
        $this->assertArrayNotHasKey('payment_method', $params);
        $this->assertArrayNotHasKey('coupon', $params);
        $this->assertCount(1, $params['items']);
        $this->assertSame(['item_id' => 'SKU1', 'item_name' => 'Foo', 'price' => 6.25, 'quantity' => 2], $params['items'][0]);
    }

    public function testMapTopLevelScalarParams(): void
    {
        $mapper = new MeasurementProtocolMapper();
        $body = $mapper->map(
            ['event' => 'login', 'method' => 'Standard', 'customer' => ['email' => 'foo@example.com']],
            new Context('1.2')
        );

        $this->assertArrayNotHasKey('user_id', $body);
        $this->assertArrayNotHasKey('consent', $body);
        $this->assertSame(['method' => 'Standard', 'engagement_time_msec' => 1], $body['events'][0]['params']);
    }

    public function testInvalidEventName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MeasurementProtocolMapper())->map(['event' => 'foo-bar'], new Context('1.2'));
    }
}
