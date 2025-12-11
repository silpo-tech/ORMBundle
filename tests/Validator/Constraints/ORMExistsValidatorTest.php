<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Validator\Constraints;

use ORMBundle\Validator\Constraints\ORMExists;
use ORMBundle\Validator\Constraints\ORMExistsValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[CoversClass(ORMExistsValidator::class)]
final class ORMExistsValidatorTest extends ConstraintValidatorTestCase
{
    use DBValidatorTestTrait;
    use ProphecyTrait;

    private function configureQueryResult(array $queryResults): void
    {
        $this->queryMock->getScalarResult()->willReturn($queryResults);
    }

    public function testValidateAddsViolationWhenArrayEntitiesNotFound(): void
    {
        $this->configureQueryResult([['field' => 'value1']]);
        $constraint = new ORMExists('stdClass', 'field', 'string');

        $this->validator->validate(['value1', 'value2'], $constraint);

        $this->buildViolation('entity.not_found')->atPath('property.path[1]')->assertRaised();
    }

    public function testValidateAddsViolationWhenEmptyArrayProvided(): void
    {
        $constraint = new ORMExists('stdClass', 'field', 'string');

        $this->validator->validate([], $constraint);

        $this->assertNoViolation();
    }

    public function testValidateAddsViolationWhenSingleEntityNotFound(): void
    {
        $this->configureQueryResult([]);
        $constraint = new ORMExists('stdClass', 'field', 'string');

        $this->validator->validate('value', $constraint);

        $this->buildViolation('entity.not_found')->assertRaised();
    }

    public function testValidateNoViolationWhenAllArrayEntitiesFound(): void
    {
        $this->configureQueryResult([['field' => 'value1'], ['field' => 'value2']]);
        $constraint = new ORMExists('stdClass', 'field', 'string');

        $this->validator->validate(['value1', 'value2'], $constraint);

        $this->assertNoViolation();
    }

    public function testValidateNoViolationWhenSingleEntityFound(): void
    {
        $this->configureQueryResult([['field' => 'value']]);
        $constraint = new ORMExists('stdClass', 'field', 'string');

        $this->validator->validate('value', $constraint);

        $this->assertNoViolation();
    }

    public function testValidateReturnsEarlyOnNullValue(): void
    {
        $constraint = new ORMExists('stdClass', 'field', 'string');

        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateReturnsEarlyOnTypeViolations(): void
    {
        // Set up expected violations for the mock validator to simulate type validation failure
        $typeConstraint = new Type('integer');
        $this->expectViolationsAt(0, 'not_an_integer', $typeConstraint);

        $constraint = new ORMExists('stdClass', 'field', 'integer');

        $this->validator->validate('not_an_integer', $constraint);

        $this->buildViolation('This value should be of type {{ type }}.')
            ->atPath('property.path.property.path')
            ->setCode('ba785a8c-82cb-4283-967c-3cf342181b40')
            ->assertRaised()
        ;
    }

    public function testValidateThrowsOnMissingEntityManager(): void
    {
        $this->registryMock->getManagerForClass('stdClass')->willReturn(null);

        $constraint = new ORMExists('stdClass', 'field', 'string');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No entity manager found for class stdClass');

        $this->validator->validate('value', $constraint);
    }

    public function testValidateThrowsOnMissingParameters(): void
    {
        $constraint = new ORMExists();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All parameters should be present in constaint');

        $this->validator->validate('value', $constraint);
    }

    public function testValidateThrowsOnWrongConstraintType(): void
    {
        $constraint = $this->prophesize(Constraint::class);

        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('value', $constraint->reveal());
    }

    protected function createValidator(): ORMExistsValidator
    {
        $this->createBasicDoctrineMocks();
        $this->configureQueryBuilderForChaining($this->qbMock, $this->queryMock->reveal());

        return new ORMExistsValidator($this->registryMock->reveal());
    }
}
