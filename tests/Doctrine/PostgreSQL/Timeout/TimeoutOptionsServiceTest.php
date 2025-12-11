<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Doctrine\PostgreSQL\Timeout;

use Doctrine\DBAL\Driver\Connection;
use ORMBundle\Doctrine\PostgreSQL\Timeout\TimeoutOptionsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

#[CoversClass(TimeoutOptionsService::class)]
class TimeoutOptionsServiceTest extends TestCase
{
    use ProphecyTrait;

    #[DataProvider('timeoutOptionsProvider')]
    public function testApplyTimeouts(?int $statementTimeout, ?int $idleTimeout, ?int $lockTimeout, array $expectedCalls): void
    {
        $service = new TimeoutOptionsService($statementTimeout, $idleTimeout, $lockTimeout);
        /** @var ObjectProphecy<Connection> $connection */
        $connection = $this->prophesize(Connection::class);

        foreach ($expectedCalls as $call) {
            $connection->exec($call)->shouldBeCalledOnce();
        }

        if (empty($expectedCalls)) {
            $connection->exec()->shouldNotBeCalled();
        }

        $service->applyTimeouts($connection->reveal());
    }

    public static function timeoutOptionsProvider(): array
    {
        return [
            'all timeouts set' => [
                5000, 10000, 15000,
                [
                    'SET statement_timeout = 5000',
                    'SET idle_in_transaction_session_timeout = 10000',
                    'SET lock_timeout = 15000',
                ],
            ],
            'no timeouts set' => [null, null, null, []],
            'zero values ignored' => [0, 0, 0, []],
            'only statement timeout' => [30000, null, null, ['SET statement_timeout = 30000']],
            'only idle timeout' => [null, 60000, null, ['SET idle_in_transaction_session_timeout = 60000']],
            'only lock timeout' => [null, null, 45000, ['SET lock_timeout = 45000']],
            'mixed values' => [5000, null, 15000, ['SET statement_timeout = 5000', 'SET lock_timeout = 15000']],
        ];
    }
}
