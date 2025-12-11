<?php

declare(strict_types=1);

namespace ORMBundle\Tests\DependencyInjection\Compiler;

use ORMBundle\DependencyInjection\Compiler\BackoffFactoryPass;
use ORMBundle\DependencyInjection\DBAL\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BackoffFactoryPassTest extends TestCase
{
    public function testProcessInjectsDefaultFactoryAndEmptyRetryOptions(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('doctrine.connections', ['default' => 'doctrine.dbal.default_connection']);
        $container->setDefinition('orm_bundle.backoff_factory.default', new Definition());

        // Create connection service definition with params
        $connectionDef = new Definition();
        $connectionDef->setArguments([[]]);
        $container->setDefinition('doctrine.dbal.default_connection', $connectionDef);

        $configDef = new Definition(Configuration::class);
        $container->setDefinition('doctrine.dbal.default_connection.configuration', $configDef);

        $pass = new BackoffFactoryPass();
        $pass->process($container);

        $calls = $configDef->getMethodCalls();
        $this->assertCount(2, $calls);

        // Check setBackoffFactory call
        $this->assertSame('setBackoffFactory', $calls[0][0]);
        $this->assertInstanceOf(Reference::class, $calls[0][1][0]);
        $this->assertSame('orm_bundle.backoff_factory.default', (string) $calls[0][1][0]);

        // Check setBackoffOptions call
        $this->assertSame('setBackoffOptions', $calls[1][0]);
        $this->assertEquals([], $calls[1][1][0]);
    }

    public function testProcessInjectsSingleConnectionDriverOptions(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('doctrine.connections', ['default' => 'doctrine.dbal.default_connection']);
        $container->setDefinition('orm_bundle.backoff_factory.default', new Definition());

        // Create single connection with factory and retry options at root level
        $backoffOptions = ['max_retries' => 5, 'initial_delay' => 100000];
        $connectionParams = [
            'driverOptions' => [
                'backoff_factory' => '@app.my_factory',
                'backoff_options' => $backoffOptions,
            ],
        ];
        $connectionDef = new Definition();
        $connectionDef->setArguments([$connectionParams]);
        $container->setDefinition('doctrine.dbal.default_connection', $connectionDef);

        $configDef = new Definition(Configuration::class);
        $container->setDefinition('doctrine.dbal.default_connection.configuration', $configDef);

        $pass = new BackoffFactoryPass();
        $pass->process($container);

        $calls = $configDef->getMethodCalls();
        $this->assertCount(2, $calls);

        // Check setBackoffFactory call
        $this->assertSame('setBackoffFactory', $calls[0][0]);
        $this->assertSame('app.my_factory', (string) $calls[0][1][0]);

        // Check setBackoffOptions call
        $this->assertSame('setBackoffOptions', $calls[1][0]);
        $this->assertEquals($backoffOptions, $calls[1][1][0]);
    }

    public function testProcessInjectsPrimaryReplicaConnectionDriverOptions(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('doctrine.connections', ['default' => 'doctrine.dbal.default_connection']);
        $container->setDefinition('orm_bundle.backoff_factory.default', new Definition());

        // Create primary-replica connection with factory and retry options in primary section
        // AND different options at root level to test priority
        $primaryBackoffOptions = ['max_retries' => 3, 'strategy' => 'exponential'];
        $rootBackoffOptions = ['max_retries' => 10, 'strategy' => 'linear'];
        $connectionParams = [
            'primary' => [
                'driverOptions' => [
                    'backoff_factory' => '@app.primary_factory',
                    'backoff_options' => $primaryBackoffOptions,
                ],
            ],
            'driverOptions' => [
                'backoff_factory' => '@app.root_factory',
                'backoff_options' => $rootBackoffOptions,
            ],
        ];
        $connectionDef = new Definition();
        $connectionDef->setArguments([$connectionParams]);
        $container->setDefinition('doctrine.dbal.default_connection', $connectionDef);

        $configDef = new Definition(Configuration::class);
        $container->setDefinition('doctrine.dbal.default_connection.configuration', $configDef);

        $pass = new BackoffFactoryPass();
        $pass->process($container);

        $calls = $configDef->getMethodCalls();
        $this->assertCount(2, $calls);

        // Check setBackoffFactory call uses primary section factory (not root)
        $this->assertSame('setBackoffFactory', $calls[0][0]);
        $this->assertSame('app.primary_factory', (string) $calls[0][1][0]);

        // Check setBackoffOptions call uses primary section (not root)
        $this->assertSame('setBackoffOptions', $calls[1][0]);
        $this->assertEquals($primaryBackoffOptions, $calls[1][1][0]);
    }

    public function testProcessDoesNothingWhenNoDoctrineConnections(): void
    {
        $container = new ContainerBuilder();
        $definitionCount = count($container->getDefinitions());

        $pass = new BackoffFactoryPass();
        $pass->process($container);

        $this->assertCount($definitionCount, $container->getDefinitions());
    }

    public function testProcessSkipsConnectionWithoutConfigurationDefinition(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('doctrine.connections', ['default' => 'doctrine.dbal.default_connection']);
        $container->setDefinition('orm_bundle.backoff_factory.default', new Definition());

        $pass = new BackoffFactoryPass();
        $pass->process($container);

        $this->assertFalse($container->hasDefinition('doctrine.dbal.default_connection.configuration'));
    }
}
