<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\AbstractString;
use PHPUnit\Framework\TestCase;

class AbstractStringTest extends TestCase
{
    /**
     * @test
     */
    public function should_be_convert_to_string()
    {
        $string = FooString::fromString('test');

        self::assertSame('test', $string->toString());
        self::assertSame('test', (string) $string);
    }
}

class FooString extends AbstractString
{
}
