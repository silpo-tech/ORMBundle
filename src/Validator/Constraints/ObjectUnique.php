<?php

declare(strict_types=1);

namespace ORMBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * Check uniqueness of Object collection by specified fields
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ObjectUnique extends Constraint
{
    public string $message = 'validation.not_unique';

    /** @var array - fields by which to determine the uniqueness
     *  If empty - will compare dto objects through '==' operator
     */
    public $uniqueBy = [];

    public function __construct(array $uniqueBy = [], ?array $groups = null, mixed $payload = null, array $options = [])
    {
        parent::__construct($options, $groups, $payload);

        $this->uniqueBy = $uniqueBy;
    }

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
