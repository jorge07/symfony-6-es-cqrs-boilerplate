<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\AbstractUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AbstractUuidTest extends TestCase
{
    /**
     * @test
     */
    public function should_be_create_by_uuid()
    {
        $baseUuid = Uuid::uuid4();
        $uuid = FooUuid::fromUuid($baseUuid);

        self::assertSame($uuid->toUuid(), $baseUuid);
    }

    /**
     * @test
     */
    public function should_be_create_by_bytes()
    {
        $bytes = Uuid::uuid4()->getBytes();
        $uuid = FooUuid::fromBytes($bytes);

        self::assertSame($uuid->getBytes(), $bytes);
    }

    /**
     * @test
     */
    public function should_be_create_by_string()
    {
        $stringUuid = 'e46db9e1-a87f-4da5-bb67-27f71a27ff00';
        $uuid = FooUuid::fromString($stringUuid);

        self::assertSame($uuid->toString(), $stringUuid);
        self::assertSame((string) $uuid, $stringUuid);
    }

    /**
     * @test
     */
    public function should_be_create_by_uuid1()
    {
        $uuid = FooUuid::uuid1(time());

        self::assertInstanceOf(FooUuid::class, $uuid);
        self::assertNotEmpty($uuid->toString());
    }

    /**
     * @test
     */
    public function should_be_create_by_uuid3()
    {
        $uuid = FooUuid::uuid3(Uuid::NAMESPACE_DNS, 'php.net');

        self::assertInstanceOf(FooUuid::class, $uuid);
        self::assertNotEmpty($uuid->toString());
    }

    /**
     * @test
     */
    public function should_be_create_by_uuid4()
    {
        $uuid = FooUuid::uuid4();

        self::assertInstanceOf(FooUuid::class, $uuid);
        self::assertNotEmpty($uuid->toString());
    }

    /**
     * @test
     */
    public function should_be_create_by_uuid5()
    {
        $uuid = FooUuid::uuid5(Uuid::NAMESPACE_DNS, 'php.net');

        self::assertInstanceOf(FooUuid::class, $uuid);
        self::assertNotEmpty($uuid->toString());
    }

    /**
     * @test
     */
    public function given_valid_string_should_return_true()
    {
        self::assertTrue(FooUuid::isValid(FooUuid::uuid4()->toString()));
    }

    /**
     * @test
     */
    public function given_invalid_string_should_return_false()
    {
        self::assertFalse(FooUuid::isValid('test'));
    }

    /**
     * @test
     */
    public function given_2_uuid_should_return_1_when_compare()
    {
        $firstUuid = FooUuid::uuid5(Uuid::NAMESPACE_DNS, 'php.net');
        $secondUuid = FooUuid::uuid5(Uuid::NAMESPACE_DNS, 'google.com');

        self::assertSame(1, $firstUuid->compareTo($secondUuid));
    }

    /**
     * @test
     */
    public function given_2_equals_uuid_should_return_true_when_check_equals()
    {
        $firstUuid = FooUuid::uuid4();
        $secondUuid = FooUuid::fromString($firstUuid->toString());

        self::assertTrue($firstUuid->equals($secondUuid));
    }

    /**
     * @test
     */
    public function given_2_not_equals_uuid_should_return_false_when_check_equals()
    {
        $firstUuid = FooUuid::uuid5(Uuid::NAMESPACE_DNS, 'php.net');
        $secondUuid = FooUuid::uuid5(Uuid::NAMESPACE_DNS, 'google.com');

        self::assertFalse($firstUuid->equals($secondUuid));
    }
}

class FooUuid extends AbstractUuid
{
}
