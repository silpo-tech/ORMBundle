<?php

declare(strict_types=1);

namespace ORMBundle\DependencyInjection;

use ORMBundle\Backoff\DefaultBackoffFactory;
use ORMBundle\DependencyInjection\DBAL\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class ORMExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Override the DBAL configuration class parameter to use our custom Configuration
        $container->setParameter('doctrine.dbal.configuration.class', Configuration::class);

        // Register default backoff factory as a service
        $container->register('orm_bundle.backoff_factory.default', DefaultBackoffFactory::class);
    }
}
