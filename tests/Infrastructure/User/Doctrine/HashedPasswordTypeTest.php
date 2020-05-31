<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\User\Doctrine;

use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Infrastructure\User\Doctrine\HashedPasswordType;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

class HashedPasswordTypeTest extends TestCase
{
    public const TYPE = 'hashed_password';

    public const BAD_DATE = 'bad_type';

    private Type $type;

    public function setUp(): void
    {
        if (!Type::hasType(self::TYPE)) {
            Type::addType(self::TYPE, HashedPasswordType::class);
        }

        $this->type = Type::getType(self::TYPE);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_hashed_password_type_when_i_get_the_sql_declaration_then_it_should_print_the_platform_string(): void
    {
        self::assertSame('VARCHAR(255)', $this->type->getSQLDeclaration([], new MySqlPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_hashed_password_type_with_a_null_value_then_it_should_return_null(): void
    {
        self::assertNull($this->type->convertToPHPValue(null, new MySqlPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_hashed_password_with_a_null_value_then_it_should_return_null(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, new MySqlPlatform()));
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_hashed_password_then_it_should_return_string_value(): void
    {
        self::assertSame(
            'hashed_password',
            $this->type->convertToDatabaseValue(HashedPassword::fromHash('hashed_password'), new MySqlPlatform())
        );
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_non_hashed_password_object_then_it_should_throw_an_exception(): void
    {
        $this->expectException(ConversionException::class);

        $this->type->convertToDatabaseValue(new \stdClass(), new MySqlPlatform());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function requires_sql_comment_hint(): void
    {
        self::assertTrue($this->type->requiresSQLCommentHint(new MySqlPlatform()));
    }
}
