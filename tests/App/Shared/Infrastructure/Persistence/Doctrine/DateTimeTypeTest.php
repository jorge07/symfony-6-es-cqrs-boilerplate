<?php

declare(strict_types=1);

namespace Tests\App\Shared\Infrastructure\Persistence\Doctrine;

use App\Shared\Infrastructure\Persistence\Doctrine\Types\DateTimeType;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Exception;

class DateTimeTypeTest extends TestCase
{
    final public const TYPE = 'lol';

    final public const BAD_DATE = 'lol';

    private \Doctrine\DBAL\Types\Type $dateTimeType;

    /**
     * @throws \Throwable
     */
    public function setUp(): void
    {
        if (!Type::hasType(self::TYPE)) {
            Type::addType(self::TYPE, DateTimeType::class);
        }

        $this->dateTimeType = Type::getType(self::TYPE);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_datetimetype_when_i_get_the_sql_declaration_then_it_should_print_the_platform_string(): void
    {
        self::assertSame('DATETIME', $this->dateTimeType->getSQLDeclaration([], new MySQLPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_datetimetype_with_a_invalid_date_then_it_should_throw_an_exception(): void
    {
        $this->expectException(ConversionException::class);

        $this->dateTimeType->convertToPHPValue(self::BAD_DATE, new MySQLPlatform());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_datetimetype_with_a_null_date_then_it_should_return_null(): void
    {
        self::assertNull($this->dateTimeType->convertToPHPValue(null, new MySQLPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_php_datetime_value_it_should_throw_an_exception(): void
    {
        $this->expectException(ConversionException::class);

        $this->dateTimeType->convertToDatabaseValue(self::BAD_DATE, new MySQLPlatform());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_php_datetimetype_with_a_null_date_then_it_should_return_null(): void
    {
        self::assertNull($this->dateTimeType->convertToDatabaseValue(null, new MySQLPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     */
    public function given_a_php_an_immutable_datetime_value_it_should_return_a_correct_format(): void
    {
        $datetimeImmutable = new \DateTimeImmutable();
        $mysqlPlatform = new MySQLPlatform();

        self::assertSame(
            $this->dateTimeType->convertToDatabaseValue($datetimeImmutable, $mysqlPlatform),
            $datetimeImmutable->format($mysqlPlatform->getDateTimeFormatString())
        );
    }
}
