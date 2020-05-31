<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Serializer\Auth;

use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use Assert\Assertion;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CredentialsNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Credentials && $this->normalizer->supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Credentials) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Credentials::class));
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Assert\AssertionFailedException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): Credentials
    {
        Assertion::keyExists($data, 'email');
        Assertion::keyExists($data, 'password');

        return new Credentials(
            Email::fromString($data['email']),
            HashedPassword::fromHash($data['password'])
        );
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return Credentials::class === $type && $this->normalizer->supportsDenormalization($data, $type, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->normalizer->hasCacheableSupportsMethod();
    }
}
