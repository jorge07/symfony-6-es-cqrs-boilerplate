<?php

declare(strict_types=1);

namespace Tests\App\Shared\Infrastructure\Persistence\Doctrine;

use App\Shared\Infrastructure\Persistence\Doctrine\Types\DateTimeType;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{
    public const TYPE = 'lol';

    public const BAD_DATE = 'lol';

    /** @var Type */
    private $dateTimeType;

    /**
     * @throws DBALException
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
        self::assertSame('DATETIME', $this->dateTimeType->getSQLDeclaration([], new MySqlPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_datetimetype_with_a_invalid_date_then_it_should_throw_an_exception(): void
    {
        $this->expectException(ConversionException::class);

        $this->dateTimeType->convertToPHPValue(self::BAD_DATE, new MySqlPlatform());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_datetimetype_with_a_null_date_then_it_should_return_null(): void
    {
        self::assertNull($this->dateTimeType->convertToPHPValue(null, new MySqlPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_php_datetime_value_it_should_throw_an_exception(): void
    {
        $this->expectException(ConversionException::class);

        $this->dateTimeType->convertToDatabaseValue(self::BAD_DATE, new MySqlPlatform());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_php_datetimetype_with_a_null_date_then_it_should_return_null(): void
    {
        self::assertNull($this->dateTimeType->convertToDatabaseValue(null, new MySqlPlatform()));
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
        $mysqlPlatform = new MySqlPlatform();

        self::assertSame(
            $this->dateTimeType->convertToDatabaseValue($datetimeImmutable, $mysqlPlatform),
            $datetimeImmutable->format($mysqlPlatform->getDateTimeFormatString())
        );
    }
}
