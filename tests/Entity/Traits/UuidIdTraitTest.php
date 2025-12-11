<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Entity\Traits;

use ORMBundle\Entity\Traits\UuidIdTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(UuidIdTrait::class)]
final class UuidIdTraitTest extends TestCase
{
    public function testGetIdReturnsNullInitially(): void
    {
        $entity = new class {
            use UuidIdTrait;
        };

        self::assertNull($entity->getId());
    }

    public function testSetIdFromString(): void
    {
        $entity = new class {
            use UuidIdTrait;
        };

        $uuidString = '01234567-89ab-cdef-0123-456789abcdef';
        $result = $entity->setId($uuidString);

        self::assertSame($entity, $result);
        self::assertInstanceOf(Uuid::class, $entity->getId());
        self::assertSame($uuidString, $entity->getId()->toRfc4122());
    }

    public function testSetIdIgnoresWhenAlreadySet(): void
    {
        $entity = new class {
            use UuidIdTrait;
        };

        $firstUuid = '01234567-89ab-cdef-0123-456789abcdef';
        $secondUuid = 'fedcba98-7654-3210-fedc-ba9876543210';

        $entity->setId($firstUuid);
        $originalId = $entity->getId();

        $entity->setId($secondUuid);

        self::assertSame($originalId, $entity->getId());
        self::assertSame($firstUuid, $entity->getId()->toRfc4122());
    }
}
