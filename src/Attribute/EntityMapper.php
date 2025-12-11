<?php

declare(strict_types=1);

namespace ORMBundle\Attribute;

use ORMBundle\Resolver\EntityMapperResolver;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PARAMETER)]
class EntityMapper extends MapEntity
{
    public function __construct(
        ?string $class = null,
        ?string $objectManager = null,
        ?string $expr = null,
        ?array $mapping = null,
        ?array $exclude = null,
        ?bool $stripNull = null,
        array|string|null $id = null,
        ?bool $evictCache = null,
        bool $disabled = false,
        string $resolver = EntityMapperResolver::class,
        public readonly string $notFoundMessage = 'entity.not_found',
    ) {
        parent::__construct(
            $class,
            $objectManager,
            $expr,
            $mapping,
            $exclude,
            $stripNull,
            $id,
            $evictCache,
            $disabled,
            $resolver,
        );
    }
}
