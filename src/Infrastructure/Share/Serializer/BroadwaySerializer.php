<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Serializer;

use Assert\Assertion;
use Broadway\Serializer\SerializationException;
use Broadway\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BroadwaySerializer implements Serializer
{
    private NormalizerInterface $normalizer;

    private DenormalizerInterface $denormalizer;

    private array $normalizationContexts;

    private array $denormalizationContexts;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        array $normalizationContexts = [],
        array $denormalizationContexts = []
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->normalizationContexts = $normalizationContexts;
        $this->denormalizationContexts = $denormalizationContexts;
    }

    /**
     * @param mixed $object
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function serialize($object): array
    {
        $class = \get_class($object);
        if (!$this->normalizer->supportsNormalization($object)) {
            throw new SerializationException(\sprintf('Object \'%s\' does not support normalization', $class));
        }

        $payload = $this->normalizer->normalize(
            $object,
            null,
            $this->normalizationContexts[$class] ?? [],
        );

        return [
            'class' => $class,
            'payload' => $payload,
        ];
    }

    /**
     * @return array|mixed|object
     *
     * @throws \Assert\AssertionFailedException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function deserialize(array $serializedObject)
    {
        Assertion::keyExists($serializedObject, 'class', "Key 'class' should be set.");
        Assertion::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        $class = $serializedObject['class'];
        $payload = $serializedObject['payload'];

        if (!$this->denormalizer->supportsDenormalization($payload, $class)) {
            throw new SerializationException(\sprintf('Object \'%s\' does not support denormalization', $class));
        }

        return $this->denormalizer->denormalize(
            $payload,
            $class,
            null,
            $this->denormalizationContexts[$class] ?? []
        );
    }
}
