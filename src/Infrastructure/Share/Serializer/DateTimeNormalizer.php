<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Serializer;

use App\Domain\Shared\ValueObject\DateTime;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof DateTime;
    }

    /**
     * @param DateTime|mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): string
    {
        if (!$object instanceof DateTime) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', DateTime::class));
        }

        return $object->toString();
    }

    /**
     * @param mixed $data
     *
     * @throws \App\Domain\Shared\Exception\DateTimeException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): DateTime
    {
        if (!\is_string($data)) {
            throw new InvalidArgumentException(\sprintf('Data expected to be a string, "%s" given.', \gettype($data)));
        }

        return DateTime::fromString($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        // Ensure Doctrine mapping from `datetime_immutable` type to DateTime::class
        return \in_array($type, [DateTime::class, \DateTimeImmutable::class]);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
