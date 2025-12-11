<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use ORMBundle\Generator\Uuid4Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Uuid4Generator::class)]
final class Uuid4GeneratorTest extends TestCase
{
    use ProphecyTrait;

    public function testGenerateIdReturnsExistingIdWhenPresent(): void
    {
        $generator = new Uuid4Generator();
        $entity = new \stdClass();
        $existingUuid = Uuid::v4();

        $em = $this->prophesize(EntityManagerInterface::class);
        $metadata = $this->prophesize(ClassMetadata::class);

        $em->getClassMetadata(\stdClass::class)->willReturn($metadata->reveal());
        $metadata->getIdentifierValues($entity)->willReturn([$existingUuid]);

        $result = $generator->generateId($em->reveal(), $entity);

        self::assertSame($existingUuid, $result);
    }

    public function testGenerateIdCreatesNewUuidV4WhenNoExistingId(): void
    {
        $generator = new Uuid4Generator();
        $entity = new \stdClass();

        $em = $this->prophesize(EntityManagerInterface::class);
        $metadata = $this->prophesize(ClassMetadata::class);

        $em->getClassMetadata(\stdClass::class)->willReturn($metadata->reveal());
        $metadata->getIdentifierValues($entity)->willReturn([]);

        $result = $generator->generateId($em->reveal(), $entity);

        self::assertInstanceOf(Uuid::class, $result);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result->toRfc4122(),
        );
    }

    public function testGenerateIdCreatesNewUuidV4WhenMultipleIds(): void
    {
        $generator = new Uuid4Generator();
        $entity = new \stdClass();

        $em = $this->prophesize(EntityManagerInterface::class);
        $metadata = $this->prophesize(ClassMetadata::class);

        $em->getClassMetadata(\stdClass::class)->willReturn($metadata->reveal());
        $metadata->getIdentifierValues($entity)->willReturn([Uuid::v4(), Uuid::v4()]);

        $result = $generator->generateId($em->reveal(), $entity);

        self::assertInstanceOf(Uuid::class, $result);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result->toRfc4122(),
        );
    }
}
