# ORM Bundle

[![CI](https://github.com/silpo-tech/ORMBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/silpo-tech/ORMBundle/actions) [![codecov](https://codecov.io/gh/silpo-tech/ORMBundle/graph/badge.svg)](https://codecov.io/gh/silpo-tech/ORMBundle) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## About

The ORM Bundle contains common used ORM classes (e.g. Validators)

## Installation ##

Require the bundle and its dependencies with composer:

```bash
$ composer require silpo-tech/orm-bundle
```

Register the bundle:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        new ORMBundle\ORMBundle(),
    );
}
```

### Traits:
```php

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Entity
{
    // @ORM\HasLifecycleCallbacks is required for @PrePersist, @PreUpdate 
    use UuidIdTrait, CreatedAtTrait, UpdatedAtTrait, UpdatedByTrait, VersionTrait;
    
    //.... other properties and methods 

}
```

## Connection Wrappers

The bundle provides connection wrappers that automatically retry failed database operations. **You must configure a wrapper class to enable retry functionality.**

### Available Wrappers

#### Standard Connection Wrapper
For single database connections:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        connections:
            default:
                wrapper_class: 'ORMBundle\Doctrine\ConnectionWrapper'
```

#### Primary-Read Replica Connection Wrapper
For primary-replica database setups:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        connections:
            default:
                wrapper_class: 'ORMBundle\Doctrine\PrimaryReadReplicaConnectionWrapper'
                primary:
                    host: 'primary-db.example.com'
                    # ... other primary config
                replica:
                    - host: 'replica1-db.example.com'
                    - host: 'replica2-db.example.com'
```

### Why Use Connection Wrappers?

Connection wrappers provide:
- **Automatic retry** on connection failures using configurable backoff strategies
- **Connection recovery** by closing and reopening failed connections  
- **Database-agnostic** connection failure detection
- **Seamless integration** with existing Doctrine configuration

**Without a wrapper class, retry options are ignored and connections will fail immediately on connection errors.**

## PostgreSQL Timeout Middleware

For PostgreSQL connections, the bundle provides middleware that automatically sets timeout parameters on connection establishment.

### Configuration

```yaml
# config/services.yaml
services:
    app.postgres_timeout_service:
        class: ORMBundle\Doctrine\PostgreSQL\Timeout\TimeoutOptionsService
        arguments:
            $statementTimeout: 30000              # 30 seconds (in milliseconds)
            $idleInTransactionSessionTimeout: 60000  # 60 seconds  
            $lockTimeout: 5000                    # 5 seconds

    app.postgres_timeout_middleware:
        class: ORMBundle\Doctrine\PostgreSQL\Timeout\Middleware
        arguments:
            - '@app.postgres_timeout_service'
        tags:
            - { name: doctrine.middleware }
```

To apply middleware only to specific connections:

```yaml
# Apply only to 'default' and 'legacy' connections
tags:
    - { name: doctrine.middleware, connection: default }
    - { name: doctrine.middleware, connection: legacy }
```

### Available Parameters

- **statement_timeout**: Maximum execution time for SQL statements (milliseconds)
- **idle_in_transaction_session_timeout**: Maximum idle time for transactions (milliseconds)
- **lock_timeout**: Maximum time to wait for locks (milliseconds)

Set parameters to `null` to skip configuration. The middleware works independently of connection wrappers.

## Connection Retry Configuration ##

The bundle automatically retries failed database connections with configurable backoff strategies.

### Default Behavior ###

Without any configuration, the bundle uses a simple fixed-delay retry:
- **5 retry attempts** (6 total attempts including initial)
- **100ms delay** between retries
- Only retries on `ConnectionException`

### Basic Configuration ###

Customize retry behavior without additional dependencies:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        connections:
            default:
                options:
                    backoff_options:
                        max_retries: 10        # Number of retries (default: 5)
                        initial_delay: 200000  # Delay in microseconds (default: 100000)
```

### Advanced Retry Strategies ###

For production environments, install the optional backoff package for advanced strategies:

```bash
$ composer require code-distortion/backoff
```

Configure a custom backoff factory:

```yaml
# config/services.yaml
services:
    app.backoff_factory:
        class: ORMBundle\Backoff\Adapter\CodeDistortionBackoffFactory

# config/packages/doctrine.yaml
doctrine:
    dbal:
        connections:
            default:
                options:
                    backoff_factory: '@app.backoff_factory'
                    backoff_options:
                        strategy: 'exponential'
                        initial_delay: 100000
                        max_retries: 10
                        max_delay: 5000000
```

### Available Strategies ###

#### Fixed (default)
Constant delay between retries. Simple and predictable.

```yaml
backoff_options:
    strategy: 'fixed'
    initial_delay: 100000  # Fixed delay in microseconds between retries (default: 100000 = 100ms)
    max_retries: 10        # Maximum number of retry attempts (default: 5)
```

**Use case:** Testing, development, or when consistent timing is required.

#### Linear
Delay increases linearly with each attempt: `initial_delay + (step * attempt_number)`

```yaml
backoff_options:
    strategy: 'linear'
    initial_delay: 100000  # Starting delay in microseconds (default: 100000 = 100ms)
    max_delay: 5000000     # Maximum delay cap in microseconds (optional, default: none)
    max_retries: 10        # Maximum number of retry attempts (default: 5)
    step: 100000           # Delay increment per attempt in microseconds (default: same as initial_delay)
```

**Example delays:** 100ms, 200ms, 300ms, 400ms, 500ms...

**Use case:** Gradual backoff when you expect quick recovery.

#### Exponential (recommended)
Delay grows exponentially: `initial_delay * factor^attempt_number`

```yaml
backoff_options:
    strategy: 'exponential'
    initial_delay: 100000  # Starting delay in microseconds (default: 100000 = 100ms)
    max_delay: 30000000    # Maximum delay cap in microseconds (optional, default: none)
    max_retries: 10        # Maximum number of retry attempts (default: 5)
    factor: 2.0            # Exponential multiplier for each attempt (default: 2.0)
```

**Example delays (factor=2.0):** 100ms, 200ms, 400ms, 800ms, 1.6s, 3.2s...

**Use case:** Most production scenarios. Quickly backs off to avoid overwhelming recovering services.

#### Polynomial
Delay grows polynomially: `initial_delay * attempt_number^power`

```yaml
backoff_options:
    strategy: 'polynomial'
    initial_delay: 100000  # Base delay in microseconds (default: 100000 = 100ms)
    max_delay: 10000000    # Maximum delay cap in microseconds (optional, default: none)
    max_retries: 10        # Maximum number of retry attempts (default: 5)
    power: 2               # Polynomial exponent (default: 2 = quadratic growth)
```

**Example delays (power=2):** 100ms, 400ms, 900ms, 1.6s, 2.5s...

**Use case:** When you need faster backoff than linear but slower than exponential.

#### Fibonacci
Delay follows Fibonacci sequence: `initial_delay * fibonacci(attempt_number)`

```yaml
backoff_options:
    strategy: 'fibonacci'
    initial_delay: 100000  # Base delay in microseconds (default: 100000 = 100ms)
    max_delay: 10000000    # Maximum delay cap in microseconds (optional, default: none)
    max_retries: 10        # Maximum number of retry attempts (default: 5)
```

**Example delays:** 100ms, 100ms, 200ms, 300ms, 500ms, 800ms, 1.3s...

**Use case:** Natural growth pattern, good balance between aggressive and conservative backoff.

#### Decorrelated Jitter
Randomized exponential backoff that prevents thundering herd problem in distributed systems.

```yaml
backoff_options:
    strategy: 'decorrelated'
    initial_delay: 100000  # Base delay in microseconds (default: 100000 = 100ms)
    max_delay: 30000000    # Maximum delay cap in microseconds (optional, default: none)
    max_retries: 10        # Maximum number of retry attempts (default: 5)
```

**Example delays:** Random values like 150ms, 380ms, 920ms, 2.1s, 5.8s... (each delay is randomized based on previous)

**Use case:** Multiple application instances retrying simultaneously. Prevents synchronized retry storms.

#### Random
Random delay within specified range.

```yaml
backoff_options:
    strategy: 'random'
    max: 1000000           # Maximum delay in microseconds (default: 500000 = 500ms)
    min: 100000            # Minimum delay in microseconds (default: 100000 = 100ms)
    max_retries: 10        # Maximum number of retry attempts (default: 5)
```

**Example delays:** Random values like 347ms, 892ms, 156ms, 723ms, 441ms... (each between min and max)

**Use case:** Simple jitter to avoid synchronized retries.

#### Sequence
Custom sequence of delays for each attempt.

```yaml
backoff_options:
    strategy: 'sequence'
    max_retries: 10        # Maximum number of retry attempts (default: 5)
    continuation: false    # If true, repeats last delay after sequence ends (default: false)
    delays: [100000, 200000, 500000, 1000000, 5000000]  # Array of delays in microseconds
```

**How it works:**
- `continuation: false` - Stops when sequence ends or `max_retries` reached (whichever comes first)
- `continuation: true` - After sequence ends, repeats last delay until `max_retries` reached

**Examples:**
- `delays: [100ms, 200ms, 500ms]`, `continuation: false`, `max_retries: 10` → 3 retries (stops at sequence end)
- `delays: [100ms, 200ms, 500ms]`, `continuation: true`, `max_retries: 10` → 10 retries (100ms, 200ms, 500ms, 500ms, 500ms...)
- `delays: [100ms, 200ms, 500ms]`, `continuation: true`, `max_retries: 2` → 2 retries (100ms, 200ms)

**Use case:** When you need precise control over each retry timing.

### Custom Backoff Factory ###

Implement your own factory for complete control:

```php
use ORMBundle\Backoff\BackoffFactoryInterface;
use ORMBundle\Backoff\BackoffInterface;

class MyBackoffFactory implements BackoffFactoryInterface
{
    public function create(array $options = []): BackoffInterface
    {
        // Return your custom BackoffInterface implementation
    }
}
```

Register it:

```yaml
# config/services.yaml
services:
    app.my_backoff_factory:
        class: App\Backoff\MyBackoffFactory

# config/packages/doctrine.yaml
doctrine:
    dbal:
        connections:
            default:
                options:
                    backoff_factory: '@app.my_backoff_factory'
```
