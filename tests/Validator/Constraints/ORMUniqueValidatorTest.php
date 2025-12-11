<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use ORMBundle\Validator\Constraints\ORMUnique;
use ORMBundle\Validator\Constraints\ORMUniqueValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[CoversClass(ORMUniqueValidator::class)]
final class ORMUniqueValidatorTest extends ConstraintValidatorTestCase
{
    use DBValidatorTestTrait;
    use ProphecyTrait;

    /** @var ObjectProphecy<ClassMetadata> */
    private ObjectProphecy $metadataMock;

    public function testValidateThrowsOnWrongConstraintType(): void
    {
        $constraint = $this->prophesize(Constraint::class);

        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(new \stdClass(), $constraint->reveal());
    }

    public function testValidateThrowsOnMissingEntityManager(): void
    {
        $this->registryMock->getManagerForClass('stdClass')->willReturn(null);

        $constraint = new ORMUnique('stdClass');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No entity manager found for class stdClass');

        $this->validator->validate(new \stdClass(), $constraint);
    }

    public function testValidateReturnsEarlyWhenNoParameters(): void
    {
        $this->qbMock->getParameters()->willReturn(new ArrayCollection());
        $constraint = new ORMUnique('stdClass');
        $dto = new \stdClass();

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateAddsViolationOnNonUniqueResultException(): void
    {
        $this->queryMock->getOneOrNullResult()->willThrow(new NonUniqueResultException());
        $constraint = new ORMUnique('stdClass', path: 'email');
        $dto = new \stdClass();

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('validation.not_unique')->atPath('property.path.email')->assertRaised();
    }

    public function testValidateAddsViolationWhenEntityFoundAndNotSame(): void
    {
        $entity = new \stdClass();
        $this->queryMock->getOneOrNullResult()->willReturn($entity);
        $this->setupPropertyAccessor($dto = new \stdClass(), $entity, false);
        $constraint = new ORMUnique('stdClass', identityField: 'id', includeFields: ['email']);

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('validation.not_unique')->assertRaised();
    }

    public function testValidateNoViolationWhenEntityFoundButSame(): void
    {
        $entity = new \stdClass();
        $this->queryMock->getOneOrNullResult()->willReturn($entity);
        $this->setupPropertyAccessor($dto = new \stdClass(), $entity, true);
        $constraint = new ORMUnique('stdClass', identityField: 'id', includeFields: ['email']);

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateNoViolationWhenNoEntityFound(): void
    {
        $this->queryMock->getOneOrNullResult()->willReturn(null);
        $constraint = new ORMUnique('stdClass');
        $dto = new \stdClass();

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateAddsViolationWhenEntityFoundAndNotSameWithPath(): void
    {
        $entity = new \stdClass();
        $this->queryMock->getOneOrNullResult()->willReturn($entity);
        $this->setupPropertyAccessor($dto = new \stdClass(), $entity, false);
        $constraint = new ORMUnique('stdClass', identityField: 'id', includeFields: ['email'], path: 'email');

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('validation.not_unique')->atPath('property.path.email')->assertRaised();
    }

    public function testValidateSkipsNullValues(): void
    {
        $dto = new \stdClass();
        $dto->email = 'test@example.com';  // This will add parameters
        $dto->name = null;  // This should trigger $dtoValue === null and hit line 106

        $this->metadataMock->hasField('email')->willReturn(true);
        $this->metadataMock->getFieldMapping('email')->willReturn(['type' => 'string']);
        $this->metadataMock->hasField('name')->willReturn(true);

        $constraint = new ORMUnique('stdClass', includeFields: ['email', 'name']);

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateSkipsNonExistentFields(): void
    {
        $dto = new \stdClass();
        $dto->email = 'test@example.com';
        $dto->name = 'John Doe';

        // First field exists and will add parameters
        $this->metadataMock->hasField('email')->willReturn(true);
        $this->metadataMock->getFieldMapping('email')->willReturn(['type' => 'string']);

        // Second field doesn't exist in entity metadata - this should hit line 106
        $this->metadataMock->hasField('nonExistentField')->willReturn(false);

        $constraint = new ORMUnique('stdClass', includeFields: ['email', 'name' => 'nonExistentField']);

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateHandlesUnsupportedFieldType(): void
    {
        $dto = new \stdClass();
        $dto->dateField = new \DateTime();
        $this->metadataMock->hasField('dateField')->willReturn(true);
        $this->metadataMock->getFieldMapping('dateField')->willReturn(['type' => 'datetime']);
        $constraint = new ORMUnique('stdClass', includeFields: ['dateField']);

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('Field type not supported for uniqueness validation')
            ->atPath('property.path.dateField')
            ->setCode('ba785a8c-82cb-4283-967c-3cf342181b40')
            ->assertRaised()
        ;
    }

    private function setupPropertyAccessor($dto, $entity, bool $isSame): void
    {
        // Set up real properties on stdClass objects
        $dto->id = $isSame ? '123' : '456';
        $dto->email = 'test@example.com';
        $entity->id = '123';

        $this->metadataMock->hasField('email')->willReturn(true);
        $this->metadataMock->getFieldMapping('email')->willReturn(['type' => 'string']);
    }

    protected function createValidator(): ORMUniqueValidator
    {
        $this->createBasicDoctrineMocks();
        $this->configureQueryBuilderForChaining($this->qbMock, $this->queryMock->reveal());

        // Set up ClassMetadata mock for ORMUnique validator
        $this->metadataMock = $this->prophesize(ClassMetadata::class);
        $this->emMock->getClassMetadata('stdClass')->willReturn($this->metadataMock->reveal());

        return new ORMUniqueValidator($this->registryMock->reveal(), new PropertyAccessor());
    }
}
