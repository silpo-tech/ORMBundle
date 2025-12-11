<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Backoff;

use ORMBundle\Backoff\BackoffInterface;
use ORMBundle\Backoff\DefaultBackoff;
use ORMBundle\Backoff\DefaultBackoffFactory;
use PHPUnit\Framework\TestCase;

class DefaultBackoffFactoryTest extends TestCase
{
    public function testCreateReturnsBackoffInterface(): void
    {
        $factory = new DefaultBackoffFactory();
        $backoff = $factory->create();

        $this->assertInstanceOf(BackoffInterface::class, $backoff);
        $this->assertInstanceOf(DefaultBackoff::class, $backoff);
    }

    public function testCreateWithCustomOptions(): void
    {
        $factory = new DefaultBackoffFactory();
        $backoff = $factory->create([
            'initial_delay' => 50000,
            'max_retries' => 3,
        ]);

        $this->assertInstanceOf(DefaultBackoff::class, $backoff);
    }

    public function testCreateWithEmptyOptions(): void
    {
        $factory = new DefaultBackoffFactory();
        $backoff = $factory->create([]);

        $this->assertInstanceOf(DefaultBackoff::class, $backoff);
    }
}
