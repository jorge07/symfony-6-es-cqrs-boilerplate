<?php

declare(strict_types=1);

namespace Tests\App\User\Domain\User\Event;

use App\Shared\Domain\ValueObject\DateTime;
use App\User\Domain\Event\UserEmailChanged;
use App\User\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class UserEmailChangedTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws \App\Shared\Domain\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     * @throws \Throwable
     */
    public function event_should_be_deserializable(): void
    {
        $event = UserEmailChanged::deserialize([
            'uuid' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'email' => 'asd@asd.asd',
            'updated_at' => DateTime::now()->toString(),
        ]);

        self::assertInstanceOf(UserEmailChanged::class, $event);
        self::assertSame('eb62dfdc-2086-11e8-b467-0ed5f89f718b', $event->uuid->toString());
        self::assertInstanceOf(Email::class, $event->email);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \App\Shared\Domain\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     * @throws \Throwable
     */
    public function event_should_fail_when_deserialize_with_wrong_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        UserEmailChanged::deserialize([
            'uuids' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'emails' => 'asd@asd.asd',
            'updated_at' => DateTime::now()->toString(),
        ]);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \App\Shared\Domain\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     * @throws \Throwable
     */
    public function event_should_be_serializable(): void
    {
        $event = UserEmailChanged::deserialize([
            'uuid' => 'eb62dfdc-2086-11e8-b467-0ed5f89f718b',
            'email' => 'asd@asd.asd',
            'updated_at' => DateTime::now()->toString(),
        ]);

        $serialized = $event->serialize();

        self::assertArrayHasKey('uuid', $serialized);
        self::assertArrayHasKey('email', $serialized);
    }
}
