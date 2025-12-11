<?php

declare(strict_types=1);

namespace ORMBundle\Doctrine\PostgreSQL\Timeout;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;

final class Middleware implements MiddlewareInterface
{
    private TimeoutOptionsService $timeoutOptionsService;

    public function __construct(TimeoutOptionsService $timeoutOptionsService)
    {
        $this->timeoutOptionsService = $timeoutOptionsService;
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver, $this->timeoutOptionsService);
    }
}
