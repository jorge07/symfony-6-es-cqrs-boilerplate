<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Share\Serializer;

use App\Infrastructure\Share\Serializer\BroadwaySerializer;
use Assert\AssertionFailedException;
use Broadway\Serializer\SerializationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BroadwaySerializerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\Stub|NormalizerInterface */
    private $mockNormalizer;

    /** @var \PHPUnit\Framework\MockObject\Stub|DenormalizerInterface */
    private $mockDenormalizer;

    protected function setUp(): void
    {
        $this->mockNormalizer = $this->createStub(NormalizerInterface::class);
        $this->mockDenormalizer = $this->createStub(DenormalizerInterface::class);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function serializing_non_supported_object_should_throw_an_exception(): void
    {
        $this->expectException(SerializationException::class);

        $this->mockNormalizer
            ->method('supportsNormalization')
            ->willReturn(false)
        ;

        $serializer = new BroadwaySerializer($this->mockNormalizer, $this->mockDenormalizer);
        $serializer->serialize(new \stdClass());
    }

    /**
     * @test
     *
     * @group unit
     */
    public function deserializing_to_non_supported_object_should_throw_an_exception(): void
    {
        $this->expectException(SerializationException::class);

        $this->mockDenormalizer
            ->method('supportsDenormalization')
            ->willReturn(false)
        ;

        $serializer = new BroadwaySerializer($this->mockNormalizer, $this->mockDenormalizer);
        $serializer->deserialize([
            'class' => \stdClass::class,
            'payload' => [],
        ]);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function invalid_serialized_object_should_throw_an_exception(): void
    {
        $this->expectException(AssertionFailedException::class);

        $serializer = new BroadwaySerializer($this->mockNormalizer, $this->mockDenormalizer);
        $serializer->deserialize([]);
    }
}
