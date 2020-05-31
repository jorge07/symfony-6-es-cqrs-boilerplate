<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Serializer\Auth;

use App\Domain\User\ValueObject\Auth\HashedPassword;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HashedPasswordNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof HashedPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = []): string
    {
        if (!$object instanceof HashedPassword) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', HashedPassword::class));
        }

        return $object->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): HashedPassword
    {
        if (!\is_string($data)) {
            throw new InvalidArgumentException(\sprintf('Data expected to be a string, "%s" given.', \gettype($data)));
        }

        return HashedPassword::fromHash($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return HashedPassword::class === $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
