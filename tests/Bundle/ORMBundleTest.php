<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Bundle;

use ORMBundle\DependencyInjection\Compiler\BackoffFactoryPass;
use ORMBundle\DependencyInjection\ORMExtension;
use ORMBundle\ORMBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(ORMBundle::class)]
final class ORMBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        $bundle = new ORMBundle();

        self::assertSame('ORMBundle', $bundle->getName());
    }

    public function testBuildRegistersBackoffFactoryPass(): void
    {
        $bundle = new ORMBundle();
        $container = new ContainerBuilder();

        $bundle->build($container);

        $passes = $container->getCompilerPassConfig()->getPasses();
        $hasBackoffFactoryPass = false;

        foreach ($passes as $pass) {
            if ($pass instanceof BackoffFactoryPass) {
                $hasBackoffFactoryPass = true;

                break;
            }
        }

        self::assertTrue($hasBackoffFactoryPass);
    }

    public function testGetContainerExtensionReturnsORMExtension(): void
    {
        $bundle = new ORMBundle();

        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(ORMExtension::class, $extension);
    }
}
