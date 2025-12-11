<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Entity\Traits;

use ORMBundle\Entity\Traits\UpdatedByTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdatedByTrait::class)]
final class UpdatedByTraitTest extends TestCase
{
    public function testGetUpdatedByReturnsNullInitially(): void
    {
        $entity = new class {
            use UpdatedByTrait;
        };

        self::assertNull($entity->getUpdatedBy());
    }

    public function testSetAndGetUpdatedBy(): void
    {
        $entity = new class {
            use UpdatedByTrait;
        };

        $result = $entity->setUpdatedBy('user123');

        self::assertSame($entity, $result);
        self::assertSame('user123', $entity->getUpdatedBy());
    }

    public function testSetUpdatedByToNull(): void
    {
        $entity = new class {
            use UpdatedByTrait;
        };

        $entity->setUpdatedBy('user123');
        $result = $entity->setUpdatedBy(null);

        self::assertSame($entity, $result);
        self::assertNull($entity->getUpdatedBy());
    }
}
