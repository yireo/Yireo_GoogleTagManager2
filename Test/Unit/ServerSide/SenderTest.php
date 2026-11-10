<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Sender;

class SenderTest extends TestCase
{
    private Curl&MockObject $curl;
    private Config&MockObject $config;

    protected function setUp(): void
    {
        $this->curl = $this->createMock(Curl::class);
        $this->config = $this->createMock(Config::class);
        $this->config->method('getMeasurementId')->willReturn('G-TEST');
        $this->config->method('getApiSecret')->willReturn('s3cr3t');
        $this->config->method('getEndpointUrl')->willReturnCallback(
            fn ($storeId, $validation) => 'https://example.com' . ($validation ? '/debug/mp/collect' : '/mp/collect')
        );
    }

    public function testSendSuccess(): void
    {
        $this->curl->expects($this->once())->method('post')
            ->with('https://example.com/mp/collect?measurement_id=G-TEST&api_secret=s3cr3t', '{"foo":"bar"}');
        $this->curl->method('getStatus')->willReturn(204);
        $this->curl->method('getBody')->willReturn('');

        $result = $this->getSender()->send(1, '{"foo":"bar"}', false);
        $this->assertTrue($result->isSuccess());
        $this->assertSame(204, $result->getStatus());
    }

    public function testSendFailure(): void
    {
        $this->curl->method('getStatus')->willReturn(500);
        $this->curl->method('getBody')->willReturn('error');

        $this->assertFalse($this->getSender()->send(1, '{}', false)->isSuccess());
    }

    public function testSendException(): void
    {
        $this->curl->method('post')->willThrowException(new \Exception('timeout'));

        $result = $this->getSender()->send(1, '{}', false);
        $this->assertFalse($result->isSuccess());
        $this->assertSame(0, $result->getStatus());
    }

    public function testValidationMessagesFail(): void
    {
        $this->curl->expects($this->once())->method('post')
            ->with($this->stringStartsWith('https://example.com/debug/mp/collect?'));
        $this->curl->method('getStatus')->willReturn(200);
        $this->curl->method('getBody')->willReturn('{"validationMessages":[{"description":"bad"}]}');

        $this->assertFalse($this->getSender()->send(1, '{}', true)->isSuccess());
    }

    private function getSender(): Sender
    {
        $curlFactory = $this->createMock(CurlFactory::class);
        $curlFactory->method('create')->willReturn($this->curl);

        return new Sender($curlFactory, $this->config, $this->createMock(Debugger::class));
    }
}
