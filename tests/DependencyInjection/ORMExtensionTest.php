<?php

declare(strict_types=1);

namespace ORMBundle\Tests\DependencyInjection;

use ORMBundle\Backoff\DefaultBackoffFactory;
use ORMBundle\DependencyInjection\DBAL\Configuration;
use ORMBundle\DependencyInjection\ORMExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ORMExtensionTest extends TestCase
{
    public function testLoadSetsConfigurationClassParameter(): void
    {
        $container = new ContainerBuilder();
        $extension = new ORMExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasParameter('doctrine.dbal.configuration.class'));
        $this->assertSame(Configuration::class, $container->getParameter('doctrine.dbal.configuration.class'));
    }

    public function testLoadRegistersDefaultBackoffFactory(): void
    {
        $container = new ContainerBuilder();
        $extension = new ORMExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition('orm_bundle.backoff_factory.default'));
        $this->assertSame(DefaultBackoffFactory::class, $container->getDefinition('orm_bundle.backoff_factory.default')->getClass());
    }
}
