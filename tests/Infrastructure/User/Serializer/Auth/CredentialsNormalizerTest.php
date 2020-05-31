<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\User\Serializer\Auth;

use App\Infrastructure\User\Serializer\Auth\CredentialsNormalizer;
use Assert\AssertionFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CredentialsNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function normalizing_non_credentials_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new CredentialsNormalizer($this->createStub(ObjectNormalizer::class));
        $normalizer->normalize(new \stdClass());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function invalid_credentials_array_should_throw_an_exception(): void
    {
        $this->expectException(AssertionFailedException::class);

        $normalizer = new CredentialsNormalizer($this->createStub(ObjectNormalizer::class));
        $normalizer->denormalize(['foo' => 'bar'], \stdClass::class);
    }
}
