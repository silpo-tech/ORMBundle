<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Backoff\Adapter;

use CodeDistortion\Backoff\Backoff;
use ORMBundle\Backoff\Adapter\CodeDistortionBackoffAdapter;
use ORMBundle\Backoff\BackoffInterface;
use PHPUnit\Framework\TestCase;

class CodeDistortionBackoffAdapterTest extends TestCase
{
    public function testImplementsBackoffInterface(): void
    {
        $backoff = $this->createMock(Backoff::class);
        $adapter = new CodeDistortionBackoffAdapter($backoff);

        $this->assertInstanceOf(BackoffInterface::class, $adapter);
    }

    public function testAttemptWithNullExceptionsCallsRetryAllExceptions(): void
    {
        $backoff = $this->createMock(Backoff::class);
        $backoff->expects($this->once())
            ->method('retryAllExceptions')
            ->willReturnSelf()
        ;
        $backoff->expects($this->once())
            ->method('attempt')
            ->with($this->isType('callable'))
            ->willReturn('success')
        ;

        $adapter = new CodeDistortionBackoffAdapter($backoff);
        $result = $adapter->attempt(static fn () => 'success', null);

        $this->assertSame('success', $result);
    }

    public function testAttemptWithEmptyArrayCallsDontRetryExceptions(): void
    {
        $backoff = $this->createMock(Backoff::class);
        $backoff->expects($this->once())
            ->method('dontRetryExceptions')
            ->willReturnSelf()
        ;
        $backoff->expects($this->once())
            ->method('attempt')
            ->with($this->isType('callable'))
            ->willReturn('success')
        ;

        $adapter = new CodeDistortionBackoffAdapter($backoff);
        $result = $adapter->attempt(static fn () => 'success', []);

        $this->assertSame('success', $result);
    }

    public function testAttemptWithSpecificExceptionsCallsRetryExceptions(): void
    {
        $exceptions = [\RuntimeException::class, \InvalidArgumentException::class];

        $backoff = $this->createMock(Backoff::class);
        $backoff->expects($this->once())
            ->method('retryExceptions')
            ->with($exceptions)
            ->willReturnSelf()
        ;
        $backoff->expects($this->once())
            ->method('attempt')
            ->with($this->isType('callable'))
            ->willReturn('success')
        ;

        $adapter = new CodeDistortionBackoffAdapter($backoff);
        $result = $adapter->attempt(static fn () => 'success', $exceptions);

        $this->assertSame('success', $result);
    }

    public function testPassesActionToBackoffAttempt(): void
    {
        $action = static fn () => 'test-result';

        $backoff = $this->createMock(Backoff::class);
        $backoff->method('retryAllExceptions')->willReturnSelf();
        $backoff->expects($this->once())
            ->method('attempt')
            ->with($action)
            ->willReturn('test-result')
        ;

        $adapter = new CodeDistortionBackoffAdapter($backoff);
        $result = $adapter->attempt($action);

        $this->assertSame('test-result', $result);
    }
}
