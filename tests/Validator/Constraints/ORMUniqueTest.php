<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Validator\Constraints;

use ORMBundle\Validator\Constraints\ORMUnique;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

#[CoversClass(ORMUnique::class)]
final class ORMUniqueTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $constraint = new ORMUnique();

        self::assertNull($constraint->entityClass);
        self::assertSame('validation.not_unique', $constraint->message);
        self::assertNull($constraint->identityField);
        self::assertNull($constraint->path);
        self::assertSame([], $constraint->includeFields);
        self::assertSame([], $constraint->excludeFields);
    }

    public function testCustomValues(): void
    {
        $constraint = new ORMUnique(
            entityClass: 'App\Entity\User',
            identityField: 'id',
            path: 'email',
            includeFields: ['name', 'email'],
            excludeFields: ['password'],
        );

        self::assertSame('App\Entity\User', $constraint->entityClass);
        self::assertSame('validation.not_unique', $constraint->message);
        self::assertSame('id', $constraint->identityField);
        self::assertSame('email', $constraint->path);
        self::assertSame(['name', 'email'], $constraint->includeFields);
        self::assertSame(['password'], $constraint->excludeFields);
    }

    public function testGetTargets(): void
    {
        $constraint = new ORMUnique();

        self::assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
