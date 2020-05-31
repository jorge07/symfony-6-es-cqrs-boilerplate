<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Serializer;

use App\Infrastructure\Share\Serializer\UuidNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class UuidNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function normalizing_non_uuid_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new UuidNormalizer();
        $normalizer->normalize(new \stdClass());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function denormalizing_to_non_uuid_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new UuidNormalizer();
        $normalizer->denormalize(['foo' => 'bar'], \stdClass::class);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function denormalizing_invalid_uuid_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new UuidNormalizer();
        $normalizer->denormalize('uuid', \stdClass::class);
    }
}
