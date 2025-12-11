<?php

declare(strict_types=1);

namespace ORMBundle\Doctrine\PostgreSQL\Timeout;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

final class Driver extends AbstractDriverMiddleware
{
    private TimeoutOptionsService $timeoutOptionsService;

    public function __construct(DriverInterface $wrappedDriver, TimeoutOptionsService $timeoutOptionsService)
    {
        $this->timeoutOptionsService = $timeoutOptionsService;

        parent::__construct($wrappedDriver);
    }

    /**
     * @return DriverConnection the database connection
     *
     * @throws Exception
     */
    public function connect(
        #[\SensitiveParameter]
        array $params,
    ) {
        $connection = parent::connect($params);

        $this->timeoutOptionsService->applyTimeouts($connection);

        return $connection;
    }
}
