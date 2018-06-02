<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject;

use App\Domain\User\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function invalid_email_should_throw_an_exception()
    {
        self::expectException(\InvalidArgumentException::class);

        Email::fromString('asd');
    }

    /**
     * @test
     *
     * @group unit
     */
    public function valid_email_should_be_able_to_cenver_to_string()
    {
        $email = Email::fromString('an@email.com');

        self::assertEquals('an@email.com', $email->toString());
        self::assertEquals('an@email.com', (string) $email);
    }
}
