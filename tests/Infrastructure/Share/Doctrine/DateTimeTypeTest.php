<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Doctrine;

use App\Infrastructure\Share\Doctrine\DateTimeType;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{
    const TYPE = 'lol';

    const BAD_DATE = 'lol';

    /**
     * @var DateTimeType
     */
    private $dateTimeType;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (!Type::hasType(self::TYPE)) {
            Type::addType(self::TYPE, DateTimeType::class);
        }

        $this->dateTimeType = Type::getType(self::TYPE);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function given_a_datetimetype_when_i_get_the_sql_declaration_then_it_should_print_the_platform_string()
    {
        self::assertSame('DATETIME', $this->dateTimeType->getSQLDeclaration([], new MySqlPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws ConversionException
     */
    public function given_a_datetimetype_with_a_invalid_date_then_it_should_throw_an_exception()
    {
        $this->expectException(ConversionException::class);

        $this->dateTimeType->convertToPHPValue(self::BAD_DATE, new MySqlPlatform());
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws ConversionException
     */
    public function given_a_datetimetype_with_a_null_date_then_it_should_return_null()
    {
        self::assertNull($this->dateTimeType->convertToPHPValue(null, new MySqlPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws ConversionException
     */
    public function given_a_php_datetime_value_it_should_throw_an_exception()
    {
        $this->expectException(ConversionException::class);

        $this->dateTimeType->convertToDatabaseValue(self::BAD_DATE, new MySqlPlatform());
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws ConversionException
     */
    public function given_a_php_datetimetype_with_a_null_date_then_it_should_return_null()
    {
        self::assertNull($this->dateTimeType->convertToDatabaseValue(null, new MySqlPlatform()));
    }
}
