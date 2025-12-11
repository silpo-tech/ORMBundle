<?php

declare(strict_types=1);

namespace ORMBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ORMUnique extends Constraint
{
    /**
     * @var string|null
     */
    public $entityClass;
    public string $message = 'validation.not_unique';
    /**
     * @var string|null
     */
    public $identityField;
    /**
     * @var string|null
     */
    public $path;
    /**
     * @var string[]
     */
    public $includeFields = [];
    /**
     * @var string[]
     */
    public $excludeFields = [];

    public function __construct(
        ?string $entityClass = null,
        ?string $identityField = null,
        ?string $path = null,
        array $includeFields = [],
        array $excludeFields = [],
        ?array $groups = null,
        mixed $payload = null,
        array $options = [],
    ) {
        parent::__construct($options, $groups, $payload);

        $this->entityClass = $entityClass;
        $this->identityField = $identityField;
        $this->path = $path;
        $this->includeFields = $includeFields;
        $this->excludeFields = $excludeFields;
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
