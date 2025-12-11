<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Doctrine\PostgreSQL\Timeout;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use ORMBundle\Doctrine\PostgreSQL\Timeout\Driver;
use ORMBundle\Doctrine\PostgreSQL\Timeout\TimeoutOptionsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

#[CoversClass(Driver::class)]
class DriverTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<DriverInterface> */
    private ObjectProphecy $wrappedDriver;
    /** @var ObjectProphecy<TimeoutOptionsService> */
    private ObjectProphecy $timeoutOptionsService;
    /** @var ObjectProphecy<DriverConnection> */
    private ObjectProphecy $connection;

    protected function setUp(): void
    {
        $this->wrappedDriver = $this->prophesize(DriverInterface::class);
        $this->timeoutOptionsService = $this->prophesize(TimeoutOptionsService::class);
        $this->connection = $this->prophesize(DriverConnection::class);
    }

    public function testConnect(): void
    {
        $params = ['host' => 'localhost', 'dbname' => 'test'];

        $this->wrappedDriver->connect($params)->willReturn($this->connection->reveal());
        $this->timeoutOptionsService->applyTimeouts($this->connection->reveal())->shouldBeCalled();

        $driver = new Driver($this->wrappedDriver->reveal(), $this->timeoutOptionsService->reveal());
        $result = $driver->connect($params);

        $this->assertSame($this->connection->reveal(), $result);
    }
}
