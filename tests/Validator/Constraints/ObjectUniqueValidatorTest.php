<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Validator\Constraints;

use ORMBundle\Validator\Constraints\ObjectUnique;
use ORMBundle\Validator\Constraints\ObjectUniqueValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[CoversClass(ObjectUniqueValidator::class)]
final class ObjectUniqueValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    protected function createValidator(): ObjectUniqueValidator
    {
        return new ObjectUniqueValidator(new PropertyAccessor());
    }

    public function testValidateThrowsOnWrongConstraintType(): void
    {
        $constraint = $this->prophesize(Constraint::class);

        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate([], $constraint->reveal());
    }

    public function testValidateReturnsEarlyOnNullValue(): void
    {
        $constraint = new ObjectUnique();

        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateAddsViolationOnNonArrayValue(): void
    {
        $constraint = new ObjectUnique();

        $this->validator->validate('not_an_array', $constraint);

        $this->buildViolation('')->setCode(Type::INVALID_TYPE_ERROR)->assertRaised();
    }

    public function testValidateAddsViolationOnNonObjectInArray(): void
    {
        $constraint = new ObjectUnique(['field']);
        $obj = new \stdClass();
        $obj->field = 'value';
        $data = [$obj, 'not_an_object'];

        $this->validator->validate($data, $constraint);

        $this->buildViolation('')->atPath('property.path[1]')->setCode(Type::INVALID_TYPE_ERROR)->assertRaised();
    }

    public function testValidateAddsViolationOnMissingProperty(): void
    {
        $constraint = new ObjectUnique(['nonExistentField']);
        $obj = new \stdClass();
        $data = [$obj];

        $this->validator->validate($data, $constraint);

        $this->buildViolation('')->atPath('property.path[0].nonExistentField')->setCode(NotBlank::IS_BLANK_ERROR)->assertRaised();
    }

    public function testValidateNoViolationOnEmptyArray(): void
    {
        $constraint = new ObjectUnique();

        $this->validator->validate([], $constraint);

        $this->assertNoViolation();
    }

    public function testValidateNoViolationOnUniqueObjects(): void
    {
        $obj1 = new \stdClass();
        $obj1->name = 'John';
        $obj2 = new \stdClass();
        $obj2->name = 'Jane';

        $constraint = new ObjectUnique(['name']);

        $this->validator->validate([$obj1, $obj2], $constraint);

        $this->assertNoViolation();
    }

    public function testValidateAddsViolationOnDuplicateObjects(): void
    {
        $obj1 = new \stdClass();
        $obj1->name = 'John';
        $obj2 = new \stdClass();
        $obj2->name = 'John';

        $constraint = new ObjectUnique(['name']);

        $this->validator->validate([$obj1, $obj2], $constraint);

        $this->buildViolation('validation.not_unique')->atPath('property.path[1]')->assertRaised();
    }

    public function testValidateWithEmptyUniqueByComparesWholeObjects(): void
    {
        $obj1 = new \stdClass();
        $obj1->name = 'John';
        $obj2 = new \stdClass();
        $obj2->name = 'John';

        $constraint = new ObjectUnique(); // Empty uniqueBy

        $this->validator->validate([$obj1, $obj2], $constraint);

        $this->buildViolation('validation.not_unique')->atPath('property.path[1]')->assertRaised();
    }

    public function testValidateWithMultipleFields(): void
    {
        $obj1 = new \stdClass();
        $obj1->name = 'John';
        $obj1->age = 25;
        $obj2 = new \stdClass();
        $obj2->name = 'John';
        $obj2->age = 30;
        $obj3 = new \stdClass();
        $obj3->name = 'John';
        $obj3->age = 25;

        $constraint = new ObjectUnique(['name', 'age']);

        $this->validator->validate([$obj1, $obj2, $obj3], $constraint);

        $this->buildViolation('validation.not_unique')->atPath('property.path[2]')->assertRaised();
    }
}
