<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Serializer;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UuidNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof UuidInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = []): string
    {
        if (!$object instanceof UuidInterface) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', UuidInterface::class));
        }

        return $object->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): UuidInterface
    {
        if (!\is_string($data)) {
            throw new InvalidArgumentException(\sprintf('Data expected to be a string, "%s" given.', \gettype($data)));
        }

        if (!Uuid::isValid($data)) {
            throw new InvalidArgumentException(\sprintf('Data is not a valid UUID, "%s" given', $data));
        }

        return Uuid::fromString($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return UuidInterface::class === $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
