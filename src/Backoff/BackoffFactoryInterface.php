<?php

declare(strict_types=1);

namespace ORMBundle\Backoff;

interface BackoffFactoryInterface
{
    public const DEFAULT_INITIAL_DELAY = 100000; // microseconds (100ms)
    public const DEFAULT_MAX_RETRIES = 5;

    public function create(array $backoffOptions = []): BackoffInterface;
}
