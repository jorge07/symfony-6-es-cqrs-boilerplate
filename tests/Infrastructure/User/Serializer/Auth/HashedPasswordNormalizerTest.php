<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\User\Serializer\Auth;

use App\Infrastructure\User\Serializer\Auth\HashedPasswordNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class HashedPasswordNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function normalizing_non_hashed_password_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new HashedPasswordNormalizer();
        $normalizer->normalize(new \stdClass());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function denormalizing_to_non_hashed_password_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new HashedPasswordNormalizer();
        $normalizer->denormalize(['foo' => 'bar'], \stdClass::class);
    }
}
