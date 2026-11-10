<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide;

use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Dispatch\DispatcherInterface;
use Yireo\GoogleTagManager2\ServerSide\Dispatch\DispatcherResolver;
use Yireo\GoogleTagManager2\ServerSide\EventPublisher;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;
use Yireo\GoogleTagManager2\ServerSide\Payload\MeasurementProtocolMapper;

class EventPublisherTest extends TestCase
{
    private Config&MockObject $config;
    private OutboxRepository&MockObject $outboxRepository;
    private DispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->config->method('isEventEnabled')->willReturnCallback(fn ($event) => $event === 'purchase');
        $this->outboxRepository = $this->createMock(OutboxRepository::class);
        $this->dispatcher = $this->createMock(DispatcherInterface::class);
    }

    public function testPublish(): void
    {
        $this->outboxRepository->method('exists')->willReturn(false);
        $this->outboxRepository->expects($this->once())->method('add')
            ->with(1, 'purchase', 'purchase:1', $this->stringContains('"client_id":"1.2"'))
            ->willReturn(10);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(10);

        $this->assertSame(10, $this->getPublisher()->publish(['event' => 'purchase'], 1, 'purchase:1', new Context('1.2')));
    }

    public function testSkipDisabledEvent(): void
    {
        $this->outboxRepository->expects($this->never())->method('add');
        $this->assertNull($this->getPublisher()->publish(['event' => 'refund'], 1, 'refund:1', new Context('1.2')));
    }

    public function testSkipWithoutConsent(): void
    {
        $this->config->method('requireConsent')->willReturn(true);
        $this->outboxRepository->expects($this->never())->method('add');

        $publisher = $this->getPublisher();
        $this->assertNull($publisher->publish(['event' => 'purchase'], 1, 'purchase:1', new Context('1.2')));
        $this->assertNull($publisher->publish(['event' => 'purchase'], 1, 'purchase:1', new Context('1.2', null, null, ['analytics' => false])));
    }

    public function testPublishWithConsent(): void
    {
        $this->config->method('requireConsent')->willReturn(true);
        $this->outboxRepository->method('add')->willReturn(11);

        $context = new Context('1.2', null, null, ['analytics' => true]);
        $this->assertSame(11, $this->getPublisher()->publish(['event' => 'purchase'], 1, 'purchase:1', $context));
    }

    public function testSkipDuplicate(): void
    {
        $this->outboxRepository->method('exists')->willReturn(true);
        $this->outboxRepository->expects($this->never())->method('add');
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->assertNull($this->getPublisher()->publish(['event' => 'purchase'], 1, 'purchase:1', new Context('1.2')));
    }

    public function testDuplicateInsertIsNotDispatched(): void
    {
        $this->outboxRepository->method('add')->willReturn(null);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->assertNull($this->getPublisher()->publish(['event' => 'purchase'], 1, 'purchase:1', new Context('1.2')));
    }

    public function testExceptionsAreCaught(): void
    {
        $this->outboxRepository->method('add')->willThrowException(new \RuntimeException('DB down'));
        $this->assertNull($this->getPublisher()->publish(['event' => 'purchase'], 1, 'purchase:1', new Context('1.2')));
    }

    private function getPublisher(): EventPublisher
    {
        $dispatcherResolver = $this->createMock(DispatcherResolver::class);
        $dispatcherResolver->method('resolve')->willReturn($this->dispatcher);

        return new EventPublisher(
            $this->config,
            new MeasurementProtocolMapper(),
            $this->outboxRepository,
            $dispatcherResolver,
            new Json(),
            $this->createMock(Debugger::class)
        );
    }
}
