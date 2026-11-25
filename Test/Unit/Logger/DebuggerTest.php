<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\Logger;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\Logger\PiiRedactor;
use Yireo\GoogleTagManager2\Util\Debug;

class DebuggerTest extends TestCase
{
    public function testDebuggerRedactsPii(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('notice')->with($this->callback(function (string $message) {
            $this->assertStringContainsString('purchase', $message);
            $this->assertStringNotContainsString('john@example.com', $message);
            $this->assertStringNotContainsString('123.456', $message);
            return true;
        }));

        $debugger = new Debugger($this->getConfig(), $logger, new PiiRedactor());
        $this->assertTrue($debugger->debug('Test', $this->getData()));
    }

    public function testUtilDebugRedactsPii(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with($this->callback(function (string $message) {
            $this->assertStringContainsString('purchase', $message);
            $this->assertStringNotContainsString('john@example.com', $message);
            $this->assertStringNotContainsString('123.456', $message);
            return true;
        }));

        $debug = new Debug($this->getConfig(), $logger, new PiiRedactor());
        $this->assertTrue($debug->debug('Test', $this->getData()));
    }

    private function getData(): array
    {
        return ['event' => 'purchase', 'user_data' => ['sha256_email_address' => 'x'], 'params' => [
            'client_id' => '123.456',
            'customer' => ['email' => 'john@example.com'],
        ]];
    }

    private function getConfig(): Config
    {
        $config = $this->createMock(Config::class);
        $config->method('isDebug')->willReturn(true);

        return $config;
    }
}
