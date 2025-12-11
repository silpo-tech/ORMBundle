<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Backoff\Adapter;

use ORMBundle\Backoff\Adapter\CodeDistortionBackoffFactory;
use ORMBundle\Backoff\BackoffInterface;
use PHPUnit\Framework\TestCase;

class CodeDistortionBackoffFactoryTest extends TestCase
{
    public function testCreateReturnsBackoffInterface(): void
    {
        $factory = new CodeDistortionBackoffFactory();
        $backoff = $factory->create();

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }

    public function testCreateWithFixedStrategy(): void
    {
        $factory = new CodeDistortionBackoffFactory();
        $backoff = $factory->create(['strategy' => 'fixed']);

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }

    public function testCreateWithExponentialStrategy(): void
    {
        $factory = new CodeDistortionBackoffFactory();
        $backoff = $factory->create([
            'strategy' => 'exponential',
            'factor' => 1.5,
        ]);

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }

    public function testCreateWithAllStrategies(): void
    {
        $factory = new CodeDistortionBackoffFactory();
        $strategies = ['fixed', 'linear', 'exponential', 'polynomial', 'fibonacci', 'decorrelated', 'random', 'sequence', 'noop', 'none'];

        foreach ($strategies as $strategy) {
            $backoff = $factory->create(['strategy' => $strategy]);
            $this->assertInstanceOf(BackoffInterface::class, $backoff);
        }
    }

    public function testCreateWithInvalidStrategyUsesDefault(): void
    {
        $factory = new CodeDistortionBackoffFactory();
        $backoff = $factory->create(['strategy' => 'invalid']);

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }

    public function testCreateWithCustomOptions(): void
    {
        $factory = new CodeDistortionBackoffFactory();
        $backoff = $factory->create([
            'initial_delay' => 50000,
            'max_retries' => 3,
        ]);

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }

    public function testCreateWithMaxDelay(): void
    {
        $factory = new CodeDistortionBackoffFactory();
        $backoff = $factory->create([
            'strategy' => 'exponential',
            'initial_delay' => 100000,
            'max_delay' => 5000000,
            'max_retries' => 10,
        ]);

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }
}
