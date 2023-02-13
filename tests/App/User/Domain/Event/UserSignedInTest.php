<?php

declare(strict_types=1);

namespace Tests\App\User\Domain\Event;

use App\User\Domain\Event\UserSignedIn;
use App\User\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class UserSignedInTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws \Assert\AssertionFailedException
     */
    public function event_should_be_deserializable(): void
    {
        $event = UserSignedIn::deserialize([
            'uuid' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'email' => 'an@email.com',
        ]);

        self::assertSame('eb62dfdc-2086-11e8-b467-0ed5f89f718b', $event->uuid->toString());
        self::assertInstanceOf(Email::class, $event->email);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Assert\AssertionFailedException
     */
    public function event_shoud_be_serializable(): void
    {
        $event = UserSignedIn::deserialize([
            'uuid' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'email' => 'an@email.com',
        ]);

        $serialized = $event->serialize();

        self::assertArrayHasKey('uuid', $serialized);
        self::assertArrayHasKey('email', $serialized);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Assert\AssertionFailedException
     */
    public function event_should_fail_when_deserialize_with_incorrect_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        UserSignedIn::deserialize([
            'notAnUuid' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'notAnEmail' => 'an@email.com',
        ]);
    }
}
