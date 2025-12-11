<?php

declare(strict_types=1);

namespace ORMBundle\Backoff\Adapter;

use CodeDistortion\Backoff\Backoff;
use ORMBundle\Backoff\BackoffInterface;

readonly class CodeDistortionBackoffAdapter implements BackoffInterface
{
    public function __construct(
        private Backoff $backoff,
    ) {
    }

    public function attempt(callable $action, ?array $exceptionsToRetry = null): mixed
    {
        $backoffInstance = clone $this->backoff;

        if (null === $exceptionsToRetry) {
            $backoffInstance->retryAllExceptions();
        } elseif (empty($exceptionsToRetry)) {
            $backoffInstance->dontRetryExceptions();
        } else {
            $backoffInstance->retryExceptions($exceptionsToRetry);
        }

        return $backoffInstance->attempt($action);
    }
}
