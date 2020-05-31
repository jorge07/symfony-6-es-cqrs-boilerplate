<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Serializer;

use App\Domain\User\ValueObject\Email;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EmailNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Email;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = []): string
    {
        if (!$object instanceof Email) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Email::class));
        }

        return $object->toString();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Assert\AssertionFailedException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): Email
    {
        if (!\is_string($data)) {
            throw new InvalidArgumentException(\sprintf('Data expected to be a string, "%s" given.', \gettype($data)));
        }

        return Email::fromString($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return Email::class === $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
