<?php

declare(strict_types=1);

namespace ORMBundle\DependencyInjection\DBAL;

use Doctrine\DBAL\Configuration as BaseConfiguration;
use ORMBundle\Backoff\BackoffFactoryInterface;
use ORMBundle\Backoff\DefaultBackoffFactory;

class Configuration extends BaseConfiguration
{
    private ?BackoffFactoryInterface $backoffFactory = null;
    private array $backoffOptions = [];

    public function getBackoffFactory(): BackoffFactoryInterface
    {
        return $this->backoffFactory ?? new DefaultBackoffFactory();
    }

    public function setBackoffFactory(BackoffFactoryInterface $backoffFactory): self
    {
        $this->backoffFactory = $backoffFactory;

        return $this;
    }

    public function getBackoffOptions(): array
    {
        return $this->backoffOptions;
    }

    public function setBackoffOptions(array $backoffOptions): self
    {
        $this->backoffOptions = $backoffOptions;

        return $this;
    }
}
