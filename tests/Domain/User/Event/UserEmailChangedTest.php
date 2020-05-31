<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Event;

use App\Domain\Shared\ValueObject\DateTime;
use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\ValueObject\Email;
use App\Tests\Domain\DomainEventTestCase;
use Ramsey\Uuid\Uuid;

class UserEmailChangedTest extends DomainEventTestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws \App\Domain\Shared\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     */
    public function event_should_be_deserializable(): void
    {
        /** @var UserEmailChanged $event */
        $event = $this->denormalizer->denormalize([
            'uuid' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'email' => 'asd@asd.asd',
            'updated_at' => DateTime::now()->toString(),
        ], UserEmailChanged::class);

        self::assertInstanceOf(UserEmailChanged::class, $event);
        self::assertSame('eb62dfdc-2086-11e8-b467-0ed5f89f718b', $event->uuid->toString());
        self::assertInstanceOf(Email::class, $event->email);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \App\Domain\Shared\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     */
    public function event_should_be_serializable(): void
    {
        $event = new UserEmailChanged(
            Uuid::fromString('eb62dfdc-2086-11e8-b467-0ed5f89f718b'),
            Email::fromString('asd@asd.asd'),
            DateTime::now()
        );

        $serialized = $this->normalizer->normalize($event);

        self::assertArrayHasKey('uuid', $serialized);
        self::assertArrayHasKey('email', $serialized);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function event_should_fail_when_deserialize_with_incorrect_keys(): void
    {
        $this->expectException(\Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException::class);

        $this->denormalizer->denormalize([
            'notAnUuid' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'notAnEmail' => 'an@email.com',
        ], UserEmailChanged::class);
    }

    /**
     * @test
     *
     * @group unit
     */
    public function event_should_fail_when_deserialize_with_incorrect_values(): void
    {
        $this->expectException(\Symfony\Component\Serializer\Exception\InvalidArgumentException::class);

        $this->denormalizer->denormalize([
            'uuid' => 'uuid',
            'email' => 'email',
        ], UserEmailChanged::class);
    }
}
