<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Validator\Constraints;

use ORMBundle\Validator\Constraints\ObjectUnique;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectUnique::class)]
final class ObjectUniqueTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $constraint = new ObjectUnique();

        $this->assertSame('validation.not_unique', $constraint->message);
        $this->assertSame([], $constraint->uniqueBy);
    }

    public function testCustomValues(): void
    {
        $uniqueBy = ['field1', 'field2'];
        $constraint = new ObjectUnique($uniqueBy);

        $this->assertSame('validation.not_unique', $constraint->message);
        $this->assertSame($uniqueBy, $constraint->uniqueBy);
    }

    public function testGetTargets(): void
    {
        $constraint = new ObjectUnique();

        $this->assertSame(ObjectUnique::PROPERTY_CONSTRAINT, $constraint->getTargets());
    }
}
