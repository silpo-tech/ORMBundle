<?php

declare(strict_types=1);

namespace ORMBundle\Validator\Constraints;

use ORMBundle\Helper\UniqueHelper;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ObjectUniqueValidator extends ConstraintValidator
{
    private PropertyAccessorInterface $accessor;

    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * @param object[]|array[]|mixed|null $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ObjectUnique) {
            throw new UnexpectedTypeException($constraint, ObjectUnique::class);
        }

        if (null === $value) {
            return;
        }

        if (!is_array($value)) {
            $this->context->buildViolation('')->setCode(Type::INVALID_TYPE_ERROR)->addViolation();

            return;
        }

        if (false === $this->validateObjects($value, $constraint->uniqueBy)) {
            return;
        }

        if (count($constraint->uniqueBy) > 0) {
            $value = $this->convertObjectToArrayByFields($value, $constraint->uniqueBy);
        }

        $notUniqueKeys = UniqueHelper::getNonUniqueValuesKeys($value);

        foreach ($notUniqueKeys as $key) {
            $this->context->buildViolation($constraint->message)->atPath("[$key]")->addViolation();
        }
    }

    private function convertObjectToArrayByFields(array $objects, array $fields): array
    {
        $fields = array_map(
            function ($object) use ($fields) {
                $values = [];
                foreach ($fields as $field) {
                    $values[$field] = $this->accessor->getValue($object, $field);
                }

                return $values;
            },
            $objects,
        );

        return $fields;
    }

    /**
     * @param string[] $fields
     *
     * @return bool - true - valid, false - not valid
     */
    private function validateObjects(array $objects, array $fields): bool
    {
        $valid = true;

        foreach ($objects as $key => $object) {
            if (!is_object($object)) {
                $valid = false;

                $this->context->buildViolation('')->atPath("[$key]")->setCode(Type::INVALID_TYPE_ERROR)->addViolation();

                continue;
            }

            foreach ($fields as $field) {
                if (false === property_exists($object, $field)) {
                    $valid = false;

                    $this->context->buildViolation('')
                        ->atPath("[$key].$field")
                        ->setCode(NotBlank::IS_BLANK_ERROR)
                        ->addViolation()
                    ;
                }
            }
        }

        return $valid;
    }
}
