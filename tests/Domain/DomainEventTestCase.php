<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class DomainEventTestCase extends KernelTestCase
{
    protected ?NormalizerInterface $normalizer;

    protected ?DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->normalizer = $this->service(NormalizerInterface::class);
        $this->denormalizer = $this->service(DenormalizerInterface::class);
    }

    /**
     * @return object|null
     */
    protected function service(string $serviceId)
    {
        return self::$container->get($serviceId);
    }
}
