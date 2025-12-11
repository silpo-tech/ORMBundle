<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Helper;

use ORMBundle\Helper\UniqueHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UniqueHelper::class)]
final class UniqueHelperTest extends TestCase
{
    public function testEmptyArrayReturnsEmpty(): void
    {
        $result = UniqueHelper::getNonUniqueValuesKeys([]);

        self::assertSame([], $result);
    }

    public function testSingleElementReturnsEmpty(): void
    {
        $result = UniqueHelper::getNonUniqueValuesKeys([['a' => 1]]);

        self::assertSame([], $result);
    }

    public function testAllUniqueArraysReturnsEmpty(): void
    {
        $data = [
            ['a' => 1],
            ['b' => 2],
            ['c' => 3],
        ];

        $result = UniqueHelper::getNonUniqueValuesKeys($data);

        self::assertSame([], $result);
    }

    public function testDuplicateArraysReturnsKeys(): void
    {
        $data = [
            ['a' => 1],
            ['b' => 2],
            ['a' => 1], // duplicate
            ['c' => 3],
            ['b' => 2],  // duplicate
        ];

        $result = UniqueHelper::getNonUniqueValuesKeys($data);

        self::assertSame([2, 4], $result);
    }

    public function testDuplicateObjectsReturnsKeys(): void
    {
        $obj1 = new \stdClass();
        $obj1->prop = 'value1';

        $obj2 = new \stdClass();
        $obj2->prop = 'value2';

        $obj3 = new \stdClass();
        $obj3->prop = 'value1'; // same as obj1

        $data = [$obj1, $obj2, $obj3];

        $result = UniqueHelper::getNonUniqueValuesKeys($data);

        self::assertSame([2], $result);
    }
}
