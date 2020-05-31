<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\User\Serializer;

use App\Infrastructure\User\Serializer\EmailNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class EmailNormalizerTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function normalizing_non_email_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new EmailNormalizer();
        $normalizer->normalize(new \stdClass());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function denormalizing_to_non_email_object_should_throw_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $normalizer = new EmailNormalizer();
        $normalizer->denormalize(['foo' => 'bar'], \stdClass::class);
    }
}
