<?php

declare(strict_types=1);

namespace ORMBundle\Backoff;

class DefaultBackoffFactory implements BackoffFactoryInterface
{
    public function create(array $backoffOptions = []): BackoffInterface
    {
        return new DefaultBackoff(
            $backoffOptions['initial_delay'] ?? self::DEFAULT_INITIAL_DELAY,
            $backoffOptions['max_retries'] ?? self::DEFAULT_MAX_RETRIES,
        );
    }
}
