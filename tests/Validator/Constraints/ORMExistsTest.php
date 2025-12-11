<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Validator\Constraints;

use ORMBundle\Validator\Constraints\ORMExists;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

#[CoversClass(ORMExists::class)]
final class ORMExistsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $constraint = new ORMExists();

        self::assertSame('entity.not_found', $constraint->message);
        self::assertNull($constraint->entityClass);
        self::assertNull($constraint->searchField);
        self::assertNull($constraint->fieldType);
    }

    public function testCustomValues(): void
    {
        $constraint = new ORMExists(
            entityClass: 'App\Entity\User',
            searchField: 'email',
            fieldType: 'string',
        );

        self::assertSame('entity.not_found', $constraint->message);
        self::assertSame('App\Entity\User', $constraint->entityClass);
        self::assertSame('email', $constraint->searchField);
        self::assertSame('string', $constraint->fieldType);
    }

    public function testGetTargets(): void
    {
        $constraint = new ORMExists();

        self::assertSame(Constraint::PROPERTY_CONSTRAINT, $constraint->getTargets());
    }
}
