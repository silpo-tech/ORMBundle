<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Backoff;

use ORMBundle\Backoff\BackoffInterface;
use ORMBundle\Backoff\DefaultBackoff;
use PHPUnit\Framework\TestCase;

class DefaultBackoffTest extends TestCase
{
    public function testImplementsBackoffInterface(): void
    {
        $backoff = new DefaultBackoff(100000, 5);

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }

    public function testSuccessfulActionReturnsResult(): void
    {
        $backoff = new DefaultBackoff(100000, 5);
        $result = $backoff->attempt(static fn () => 'success');

        $this->assertSame('success', $result);
    }

    public function testRetryAnyExceptionByDefault(): void
    {
        $attempts = 0;
        $backoff = new DefaultBackoff(0, 3);

        $result = $backoff->attempt(static function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 3) {
                throw new \RuntimeException('fail');
            }

            return 'success';
        });

        $this->assertSame('success', $result);
        $this->assertSame(3, $attempts);
    }

    public function testMaxAttemptsReached(): void
    {
        $attempts = 0;
        $backoff = new DefaultBackoff(0, 2);

        $this->expectException(\RuntimeException::class);

        $backoff->attempt(static function () use (&$attempts) {
            ++$attempts;

            throw new \RuntimeException('fail');
        });

        $this->assertSame(2, $attempts);
    }

    public function testRetrySpecificExceptions(): void
    {
        $attempts = 0;
        $backoff = new DefaultBackoff(0, 3);

        $result = $backoff->attempt(
            static function () use (&$attempts) {
                ++$attempts;
                if ($attempts < 2) {
                    throw new \RuntimeException('retry this');
                }

                return 'success';
            },
            [\RuntimeException::class],
        );

        $this->assertSame('success', $result);
        $this->assertSame(2, $attempts);
    }

    public function testDontRetryUnspecifiedException(): void
    {
        $attempts = 0;
        $backoff = new DefaultBackoff(0, 3);

        $this->expectException(\InvalidArgumentException::class);

        $backoff->attempt(
            static function () use (&$attempts) {
                ++$attempts;

                throw new \InvalidArgumentException('dont retry this');
            },
            [\RuntimeException::class],
        );

        $this->assertSame(1, $attempts);
    }

    public function testEmptyExceptionArrayMeansNoRetries(): void
    {
        $attempts = 0;
        $backoff = new DefaultBackoff(0, 3);

        $this->expectException(\RuntimeException::class);

        $backoff->attempt(
            static function () use (&$attempts) {
                ++$attempts;

                throw new \RuntimeException('no retries');
            },
            [],
        );

        $this->assertSame(1, $attempts);
    }
}
