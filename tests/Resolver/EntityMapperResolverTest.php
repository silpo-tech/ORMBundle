<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Resolver;

use ORMBundle\Attribute\EntityMapper;
use ORMBundle\Resolver\EntityMapperResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(EntityMapperResolver::class)]
final class EntityMapperResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testResolveSuccessReturnsResult(): void
    {
        $entityValueResolver = $this->prophesize(EntityValueResolver::class);
        $request = new Request();
        $argument = $this->prophesize(ArgumentMetadata::class);

        $expectedResult = ['entity'];
        $entityValueResolver->resolve($request, $argument->reveal())->willReturn($expectedResult);

        $resolver = new EntityMapperResolver($entityValueResolver->reveal());
        $result = $resolver->resolve($request, $argument->reveal());

        self::assertSame($expectedResult, $result);
    }

    public function testResolveNotFoundThrowsCustomMessage(): void
    {
        $entityValueResolver = $this->prophesize(EntityValueResolver::class);
        $request = new Request();
        $argument = $this->prophesize(ArgumentMetadata::class);

        $attribute = new EntityMapper(notFoundMessage: 'custom.not_found');

        $entityValueResolver->resolve($request, $argument->reveal())
            ->willThrow(new NotFoundHttpException('original message'))
        ;

        $argument->getAttributesOfType(EntityMapper::class, ArgumentMetadata::IS_INSTANCEOF)
            ->willReturn([$attribute])
        ;

        $resolver = new EntityMapperResolver($entityValueResolver->reveal());

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('custom.not_found');

        iterator_to_array($resolver->resolve($request, $argument->reveal()));
    }
}
