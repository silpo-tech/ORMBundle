<?php

declare(strict_types=1);

namespace ORMBundle\Doctrine\PostgreSQL\Timeout;

use Doctrine\DBAL\Driver\Connection;

class TimeoutOptionsService
{
    private ?int $statementTimeout = null;
    private ?int $idleInTransactionSessionTimeout = null;
    private ?int $lockTimeout = null;

    public function __construct(
        ?int $statementTimeout = null,
        ?int $idleInTransactionSessionTimeout = null,
        ?int $lockTimeout = null,
    ) {
        $this->statementTimeout = $statementTimeout;
        $this->idleInTransactionSessionTimeout = $idleInTransactionSessionTimeout;
        $this->lockTimeout = $lockTimeout;
    }

    public function applyTimeouts(Connection $connection): void
    {
        $this->applyParameter($connection, 'statement_timeout', $this->statementTimeout);
        $this->applyParameter($connection, 'idle_in_transaction_session_timeout', $this->idleInTransactionSessionTimeout);
        $this->applyParameter($connection, 'lock_timeout', $this->lockTimeout);
    }

    private function applyParameter(Connection $connection, string $parameter, ?int $value): void
    {
        if (!$value) {
            return;
        }

        $connection->exec(sprintf('SET %s = %d', $parameter, $value));
    }
}
