<?php

declare(strict_types=1);

namespace ORMBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BackoffFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.connections')) {
            return;
        }

        $connections = $container->getParameter('doctrine.connections');

        foreach ($connections as $name => $serviceId) {
            $configurationId = "doctrine.dbal.{$name}_connection.configuration";

            if (!$container->hasDefinition($configurationId)) {
                continue;
            }

            $connectionDef = $container->getDefinition($serviceId);
            $connectionParams = $connectionDef->getArgument(0);

            $driverOptions = $this->extractDriverOptions($connectionParams);
            $factoryServiceId = $driverOptions['backoff_factory'] ?? null;
            $backoffOptions = $driverOptions['backoff_options'] ?? [];

            $factoryRef = $factoryServiceId
                ? new Reference(ltrim($factoryServiceId, '@'))
                : new Reference('orm_bundle.backoff_factory.default');

            $configurationDef = $container->getDefinition($configurationId);
            $configurationDef->addMethodCall('setBackoffFactory', [$factoryRef]);
            $configurationDef->addMethodCall('setBackoffOptions', [$backoffOptions]);
        }
    }

    private function extractDriverOptions(array $connectionParams): array
    {
        // Primary-replica connection: options in primary section
        if (isset($connectionParams['primary']['driverOptions'])) {
            return $connectionParams['primary']['driverOptions'];
        }

        // Standard connection: options at root level
        if (isset($connectionParams['driverOptions'])) {
            return $connectionParams['driverOptions'];
        }

        return [];
    }
}
