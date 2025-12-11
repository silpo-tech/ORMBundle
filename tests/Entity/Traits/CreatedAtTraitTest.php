<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Entity\Traits;

use ORMBundle\Entity\Traits\CreatedAtTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

#[CoversClass(CreatedAtTrait::class)]
final class CreatedAtTraitTest extends TestCase
{
    public function testSetAndGetCreatedAt(): void
    {
        $entity = new class {
            use CreatedAtTrait;
        };

        $dateTime = new \DateTime('2023-01-01 12:00:00');
        $result = $entity->setCreatedAt($dateTime);

        self::assertSame($entity, $result);
        self::assertSame($dateTime, $entity->getCreatedAt());
    }

    public function testInitializeCreatedAtSetsCurrentTime(): void
    {
        $entity = new class {
            use CreatedAtTrait;
        };

        $fixedTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $mockClock = new MockClock($fixedTime);
        $entity->setClock($mockClock);

        $entity->initializeCreatedAt();

        self::assertEquals($fixedTime, $entity->getCreatedAt());
    }

    public function testInitializeCreatedAtIgnoresWhenAlreadySet(): void
    {
        $entity = new class {
            use CreatedAtTrait;
        };

        $originalTime = new \DateTime('2023-01-01 10:00:00');
        $entity->setCreatedAt($originalTime);

        $fixedTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $mockClock = new MockClock($fixedTime);
        $entity->setClock($mockClock);

        $entity->initializeCreatedAt();

        self::assertSame($originalTime, $entity->getCreatedAt());
    }
}
