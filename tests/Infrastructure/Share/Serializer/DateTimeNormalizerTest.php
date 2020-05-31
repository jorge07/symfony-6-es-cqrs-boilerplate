<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Serializer;

use App\Infrastructure\Share\Serializer\DateTimeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class DateTimeNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function normalizing_non_datetime_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new DateTimeNormalizer();
        $normalizer->normalize(new \stdClass());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function denormalizing_to_non_datetime_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new DateTimeNormalizer();
        $normalizer->denormalize(['foo' => 'bar'], \stdClass::class);
    }
}
