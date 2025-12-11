<?php

declare(strict_types=1);

namespace ORMBundle;

use ORMBundle\DependencyInjection\Compiler\BackoffFactoryPass;
use ORMBundle\DependencyInjection\ORMExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ORMBundle.
 */
class ORMBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new BackoffFactoryPass());
    }

    public function getContainerExtension(): ORMExtension
    {
        return new ORMExtension();
    }
}
