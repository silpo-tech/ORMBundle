<?php

declare(strict_types=1);

namespace ORMBundle\Resolver;

use ORMBundle\Attribute\EntityMapper;
use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityMapperResolver implements ValueResolverInterface
{
    public function __construct(
        #[Autowire(service: 'doctrine.orm.entity_value_resolver')]
        private readonly EntityValueResolver $entityValueResolver,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        try {
            return $this->entityValueResolver->resolve($request, $argument);
        } catch (NotFoundHttpException) {
            $attribute = $argument->getAttributesOfType(EntityMapper::class, ArgumentMetadata::IS_INSTANCEOF)[0];

            throw new NotFoundHttpException($attribute->notFoundMessage);
        }
    }
}
