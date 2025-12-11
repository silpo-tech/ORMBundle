<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Attribute;

use ORMBundle\Attribute\EntityMapper;
use ORMBundle\Resolver\EntityMapperResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EntityMapper::class)]
final class EntityMapperTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $attribute = new EntityMapper();

        self::assertSame('entity.not_found', $attribute->notFoundMessage);
        self::assertSame(EntityMapperResolver::class, $attribute->resolver);
    }

    public function testCustomValues(): void
    {
        $attribute = new EntityMapper(
            resolver: 'CustomResolver',
            notFoundMessage: 'custom.not_found',
        );

        self::assertSame('custom.not_found', $attribute->notFoundMessage);
        self::assertSame('CustomResolver', $attribute->resolver);
    }
}
