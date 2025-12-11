<?php

declare(strict_types=1);

namespace ORMBundle\Backoff\Adapter;

use CodeDistortion\Backoff\Backoff;
use ORMBundle\Backoff\BackoffFactoryInterface;
use ORMBundle\Backoff\BackoffInterface;

class CodeDistortionBackoffFactory implements BackoffFactoryInterface
{
    private const DEFAULT_EXPONENTIAL_FACTOR = 2.0;
    private const DEFAULT_POLYNOMIAL_POWER = 2.0;
    private const DEFAULT_FIBONACCI_INCLUDE_FIRST = true;
    private const DEFAULT_DECORRELATED_MULTIPLIER = 3.0;
    private const DEFAULT_RANDOM_MAX_MULTIPLIER = 5;
    private const DEFAULT_SEQUENCE_CONTINUATION = false;

    public function create(array $backoffOptions = []): BackoffInterface
    {
        $strategy = BackoffStrategy::tryFrom($backoffOptions['strategy'] ?? 'fixed') ?? BackoffStrategy::Fixed;
        $initialDelay = $backoffOptions['initial_delay'] ?? self::DEFAULT_INITIAL_DELAY;
        $maxRetries = $backoffOptions['max_retries'] ?? self::DEFAULT_MAX_RETRIES;
        $maxDelay = $backoffOptions['max_delay'] ?? null;

        $backoff = match ($strategy) {
            BackoffStrategy::Fixed => Backoff::fixedUs($initialDelay),

            BackoffStrategy::Linear => Backoff::linearUs(
                $initialDelay,
                $backoffOptions['step'] ?? $initialDelay,
            ),

            BackoffStrategy::Exponential => Backoff::exponentialUs(
                $initialDelay,
                $backoffOptions['factor'] ?? self::DEFAULT_EXPONENTIAL_FACTOR,
            ),

            BackoffStrategy::Polynomial => Backoff::polynomialUs(
                $initialDelay,
                $backoffOptions['power'] ?? self::DEFAULT_POLYNOMIAL_POWER,
            ),

            BackoffStrategy::Fibonacci => Backoff::fibonacciUs(
                $initialDelay,
                $backoffOptions['include_first'] ?? self::DEFAULT_FIBONACCI_INCLUDE_FIRST,
            ),

            BackoffStrategy::Decorrelated => Backoff::decorrelatedUs(
                $initialDelay,
                $backoffOptions['multiplier'] ?? self::DEFAULT_DECORRELATED_MULTIPLIER,
            ),

            BackoffStrategy::Random => Backoff::randomUs(
                $backoffOptions['min'] ?? $initialDelay,
                $backoffOptions['max'] ?? $initialDelay * self::DEFAULT_RANDOM_MAX_MULTIPLIER,
            ),

            BackoffStrategy::Sequence => Backoff::sequenceUs(
                $backoffOptions['delays'] ?? [$initialDelay],
                $backoffOptions['continuation'] ?? self::DEFAULT_SEQUENCE_CONTINUATION,
            ),

            BackoffStrategy::Noop => Backoff::noop(),

            BackoffStrategy::None => Backoff::none(),
        };

        $backoff = $backoff
            ->noJitter()
            ->maxAttempts($maxRetries)
        ;

        if (null !== $maxDelay) {
            $backoff = $backoff->maxDelay($maxDelay);
        }

        return new CodeDistortionBackoffAdapter($backoff);
    }
}
