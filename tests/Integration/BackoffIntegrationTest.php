<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use ORMBundle\Backoff\Adapter\CodeDistortionBackoffFactory;
use ORMBundle\Backoff\DefaultBackoffFactory;
use ORMBundle\DependencyInjection\DBAL\Configuration;
use ORMBundle\Doctrine\ConnectionWrapper;
use ORMBundle\ORMBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

class BackoffIntegrationTest extends TestCase
{
    public function testDefaultBackoffFactory(): void
    {
        $kernel = $this->createKernel('default_backoff.yaml', ['default']);
        $kernel->boot();
        $container = $kernel->getContainer();

        $connection = $container->get('doctrine.dbal.default_connection');
        $this->assertInstanceOf(ConnectionWrapper::class, $connection);

        $configuration = $container->get('test.doctrine.dbal.default_connection.configuration');
        $this->assertInstanceOf(Configuration::class, $configuration);
        $this->assertInstanceOf(DefaultBackoffFactory::class, $configuration->getBackoffFactory());

        $result = $connection->executeQuery('SELECT 1 as test');
        $this->assertSame(1, $result->fetchOne());

        $kernel->shutdown();
    }

    public function testCustomBackoffFactoryWithMultipleConnections(): void
    {
        $kernel = $this->createKernel('custom_backoff.yaml', ['default', 'secondary']);
        $kernel->boot();
        $container = $kernel->getContainer();

        // Test default connection
        $defaultConnection = $container->get('doctrine.dbal.default_connection');
        $this->assertInstanceOf(ConnectionWrapper::class, $defaultConnection);

        $defaultConfig = $container->get('test.doctrine.dbal.default_connection.configuration');
        $this->assertInstanceOf(Configuration::class, $defaultConfig);
        $this->assertInstanceOf(CodeDistortionBackoffFactory::class, $defaultConfig->getBackoffFactory());

        $result = $defaultConnection->executeQuery('SELECT 1 as test');
        $this->assertSame(1, $result->fetchOne());

        // Test secondary connection
        $secondaryConnection = $container->get('doctrine.dbal.secondary_connection');
        $this->assertInstanceOf(ConnectionWrapper::class, $secondaryConnection);

        $secondaryConfig = $container->get('test.doctrine.dbal.secondary_connection.configuration');
        $this->assertInstanceOf(Configuration::class, $secondaryConfig);
        $this->assertInstanceOf(CodeDistortionBackoffFactory::class, $secondaryConfig->getBackoffFactory());

        $result = $secondaryConnection->executeQuery('SELECT 2 as test');
        $this->assertSame(2, $result->fetchOne());

        $kernel->shutdown();
    }

    private function createKernel(string $configFile, array $connections): Kernel
    {
        return new class($configFile, $connections) extends Kernel {
            public function __construct(
                private string $configFile,
                private array $connections,
            ) {
                parent::__construct('test', true);
            }

            public function registerBundles(): iterable
            {
                return [
                    new DoctrineBundle(),
                    new ORMBundle(),
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader): void
            {
                $loader->load(__DIR__.'/config/'.$this->configFile);
            }

            protected function build(ContainerBuilder $container): void
            {
                // Make configuration services public for testing
                foreach ($this->connections as $name) {
                    $container->register("test.doctrine.dbal.{$name}_connection.configuration", Configuration::class)
                        ->setPublic(true)
                        ->setFactory([new Reference("doctrine.dbal.{$name}_connection"), 'getConfiguration'])
                    ;
                }
            }

            public function getCacheDir(): string
            {
                return sys_get_temp_dir().'/orm_bundle_test/'.$this->configFile.'/cache';
            }

            public function getLogDir(): string
            {
                return sys_get_temp_dir().'/orm_bundle_test/'.$this->configFile.'/logs';
            }
        };
    }
}
