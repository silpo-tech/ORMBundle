<?php

declare(strict_types=1);

namespace ORMBundle\DependencyInjection;

use ORMBundle\Backoff\DefaultBackoffFactory;
use ORMBundle\DependencyInjection\DBAL\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ORMExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Override the DBAL configuration class parameter to use our custom Configuration
        $container->setParameter('doctrine.dbal.configuration.class', Configuration::class);

        // Register default backoff factory as a service
        $container->register('orm_bundle.backoff_factory.default', DefaultBackoffFactory::class);
    }
}
