<?php

declare(strict_types=1);

namespace ORMBundle\Tests\DependencyInjection;

use Doctrine\DBAL\Configuration as BaseConfiguration;
use ORMBundle\Backoff\BackoffFactoryInterface;
use ORMBundle\Backoff\DefaultBackoffFactory;
use ORMBundle\DependencyInjection\DBAL\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testExtendsDoctrineConfiguration(): void
    {
        $config = new Configuration();

        $this->assertInstanceOf(BaseConfiguration::class, $config);
    }

    public function testGetBackoffFactoryReturnsDefaultWhenNotSet(): void
    {
        $config = new Configuration();

        $this->assertInstanceOf(DefaultBackoffFactory::class, $config->getBackoffFactory());
    }

    public function testSetBackoffFactoryReturnsThis(): void
    {
        $config = new Configuration();
        $customFactory = $this->createMock(BackoffFactoryInterface::class);

        $result = $config->setBackoffFactory($customFactory);

        $this->assertSame($config, $result);
    }

    public function testGetBackoffFactoryReturnsSetFactory(): void
    {
        $config = new Configuration();
        $customFactory = $this->createMock(BackoffFactoryInterface::class);

        $config->setBackoffFactory($customFactory);

        $this->assertSame($customFactory, $config->getBackoffFactory());
    }

    public function testGetBackoffOptionsReturnsEmptyArrayByDefault(): void
    {
        $config = new Configuration();

        $this->assertEquals([], $config->getBackoffOptions());
    }

    public function testSetBackoffOptionsReturnsThis(): void
    {
        $config = new Configuration();
        $backoffOptions = ['max_retries' => 5];

        $result = $config->setBackoffOptions($backoffOptions);

        $this->assertSame($config, $result);
    }

    public function testGetBackoffOptionsReturnsSetOptions(): void
    {
        $config = new Configuration();
        $backoffOptions = ['max_retries' => 5, 'initial_delay' => 100000];

        $config->setBackoffOptions($backoffOptions);

        $this->assertEquals($backoffOptions, $config->getBackoffOptions());
    }
}
