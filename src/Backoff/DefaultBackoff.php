<?php

declare(strict_types=1);

namespace ORMBundle\Backoff;

readonly class DefaultBackoff implements BackoffInterface
{
    public function __construct(
        private int $delay,
        private int $maxAttempts,
    ) {
    }

    public function attempt(callable $action, ?array $exceptionsToRetry = null): mixed
    {
        $attempt = 0;

        do {
            try {
                return $action();
            } catch (\Throwable $e) {
                if (!$this->shouldRetry($e, $exceptionsToRetry, ++$attempt)) {
                    throw $e;
                }

                usleep($this->delay);
            }
        } while ($attempt < $this->maxAttempts);

        return null; // @codeCoverageIgnore
    }

    private function shouldRetry(\Throwable $exception, ?array $exceptionsToRetry, int $attempt): bool
    {
        if ($attempt >= $this->maxAttempts) {
            return false;
        }

        if (null === $exceptionsToRetry) {
            return true;
        }

        foreach ($exceptionsToRetry as $exceptionClass) {
            if ($exception instanceof $exceptionClass) {
                return true;
            }
        }

        return false;
    }
}
