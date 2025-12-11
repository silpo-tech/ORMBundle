<?php

declare(strict_types=1);

namespace ORMBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * Checks if $entityClass instance exists in database by $searchField value.
 * If $fieldType and values type don't match - will return Assert/Type() violation.
 *
 * Can be set on bool|int|string or array. If set on array - array should contain only $searchField values.
 * Example: $searchField - id; value 1 or [1,2,3,4]
 * If value is array - it will validate that all values in array exist
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ORMExists extends Constraint
{
    public string $message = 'entity.not_found';

    /** @var string|null */
    public $entityClass;

    /** @var string|null - field by which to search */
    public $searchField;

    /** @var string|null - type of */
    public $fieldType;

    public function __construct(
        ?string $entityClass = null,
        ?string $searchField = null,
        ?string $fieldType = null,
        ?array $groups = null,
        mixed $payload = null,
        array $options = [],
    ) {
        parent::__construct($options, $groups, $payload);

        $this->entityClass = $entityClass;
        $this->searchField = $searchField;
        $this->fieldType = $fieldType;
    }

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
