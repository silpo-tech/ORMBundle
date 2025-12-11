<?php

declare(strict_types=1);

namespace ORMBundle\Backoff\Adapter;

enum BackoffStrategy: string
{
    case Fixed = 'fixed';
    case Linear = 'linear';
    case Exponential = 'exponential';
    case Polynomial = 'polynomial';
    case Fibonacci = 'fibonacci';
    case Decorrelated = 'decorrelated';
    case Random = 'random';
    case Sequence = 'sequence';
    case Noop = 'noop';
    case None = 'none';
}
