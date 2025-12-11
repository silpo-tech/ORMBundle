<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Entity\Traits;

use ORMBundle\Entity\Traits\UpdatedAtTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

#[CoversClass(UpdatedAtTrait::class)]
final class UpdatedAtTraitTest extends TestCase
{
    public function testGetUpdatedAtReturnsNullInitially(): void
    {
        $entity = new class {
            use UpdatedAtTrait;
        };

        self::assertNull($entity->getUpdatedAt());
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $entity = new class {
            use UpdatedAtTrait;
        };

        $dateTime = new \DateTime('2023-01-01 12:00:00');
        $result = $entity->setUpdatedAt($dateTime);

        self::assertSame($entity, $result);
        self::assertSame($dateTime, $entity->getUpdatedAt());
    }

    public function testPrePersistUpdateAtSetsCurrentTime(): void
    {
        $entity = new class {
            use UpdatedAtTrait;
        };

        $fixedTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $mockClock = new MockClock($fixedTime);
        $entity->setClock($mockClock);

        $entity->prePersistUpdateAt();

        self::assertEquals($fixedTime, $entity->getUpdatedAt());
    }

    public function testPrePersistUpdateAtIgnoresWhenAlreadySet(): void
    {
        $entity = new class {
            use UpdatedAtTrait;
        };

        $originalTime = new \DateTime('2023-01-01 10:00:00');
        $entity->setUpdatedAt($originalTime);

        $fixedTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $mockClock = new MockClock($fixedTime);
        $entity->setClock($mockClock);

        $entity->prePersistUpdateAt();

        self::assertSame($originalTime, $entity->getUpdatedAt());
    }

    public function testPreUpdateUpdateAtAlwaysUpdates(): void
    {
        $entity = new class {
            use UpdatedAtTrait;
        };

        $originalTime = new \DateTime('2023-01-01 10:00:00');
        $entity->setUpdatedAt($originalTime);

        $fixedTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $mockClock = new MockClock($fixedTime);
        $entity->setClock($mockClock);

        $entity->preUpdateUpdateAt();

        self::assertEquals($fixedTime, $entity->getUpdatedAt());
        self::assertNotSame($originalTime, $entity->getUpdatedAt());
    }
}
