<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Doctrine\PostgreSQL\Timeout;

use Doctrine\DBAL\Driver as DriverInterface;
use ORMBundle\Doctrine\PostgreSQL\Timeout\Driver;
use ORMBundle\Doctrine\PostgreSQL\Timeout\Middleware;
use ORMBundle\Doctrine\PostgreSQL\Timeout\TimeoutOptionsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

#[CoversClass(Middleware::class)]
class MiddlewareTest extends TestCase
{
    use ProphecyTrait;

    public function testWrap(): void
    {
        $driver = $this->prophesize(DriverInterface::class);
        $timeoutOptionsService = $this->prophesize(TimeoutOptionsService::class);

        $middleware = new Middleware($timeoutOptionsService->reveal());
        $wrappedDriver = $middleware->wrap($driver->reveal());

        $this->assertInstanceOf(Driver::class, $wrappedDriver);
    }
}
