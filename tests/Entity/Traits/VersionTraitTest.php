<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Entity\Traits;

use ORMBundle\Entity\Traits\VersionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VersionTrait::class)]
final class VersionTraitTest extends TestCase
{
    public function testGetVersionReturnsSetValue(): void
    {
        $entity = new class {
            use VersionTrait;

            public function __construct()
            {
                $this->version = 5;
            }
        };

        self::assertSame(5, $entity->getVersion());
    }

    public function testGetVersionReturnsInitialValue(): void
    {
        $entity = new class {
            use VersionTrait;

            public function __construct()
            {
                $this->version = 1;
            }
        };

        self::assertSame(1, $entity->getVersion());
    }
}
