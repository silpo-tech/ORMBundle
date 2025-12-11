<?php

declare(strict_types=1);

namespace ORMBundle\Backoff;

interface BackoffInterface
{
    /**
     * @param callable                             $action            Action to execute with retry logic
     * @param array<class-string<\Throwable>>|null $exceptionsToRetry
     *
     * $exceptionsToRetry === null : retry any exception
     * $exceptionsToRetry === [] : no retries on exception
     * $exceptionsToRetry === [\InvalidArgumentException::class] : retry only specified exceptions
     */
    public function attempt(callable $action, ?array $exceptionsToRetry = null): mixed;
}
