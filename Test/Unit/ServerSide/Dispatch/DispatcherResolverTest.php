<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide\Dispatch;

use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Dispatch\CronDispatcher;
use Yireo\GoogleTagManager2\ServerSide\Dispatch\DispatcherResolver;
use Yireo\GoogleTagManager2\ServerSide\Dispatch\QueueDispatcher;

class DispatcherResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getDispatchMode')->willReturnOnConsecutiveCalls('cron', 'queue');
        $cronDispatcher = new CronDispatcher();
        $queueDispatcher = $this->createMock(QueueDispatcher::class);

        $resolver = new DispatcherResolver($config, $cronDispatcher, $queueDispatcher);
        $this->assertSame($cronDispatcher, $resolver->resolve());
        $this->assertSame($queueDispatcher, $resolver->resolve());
    }
}
