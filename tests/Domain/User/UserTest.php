<?php

namespace App\Tests\Domain\User;

use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function given_a_valid_email_it_should_create_a_user_instance()
    {
        $emailString = 'lol@aso.maximo';

        $user = User::create(Uuid::uuid4(), Email::fromString($emailString));

        self::assertEquals($emailString, $user->email());
        self::assertNotNull($user->uuid());

        $events = $user->getUncommittedEvents();

        self::assertCount(1, $events->getIterator(), 'Only one event should be in the buffer');

        /** @var DomainMessage $event */
        $event = $events->getIterator()->current();

        self::assertInstanceOf(UserWasCreated::class, $event->getPayload(), 'First event should be UserWasCreated');
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_new_email_it_should_change_if_not_eq_to_prev_email()
    {
        $emailString = 'lol@aso.maximo';

        $user = User::create(Uuid::uuid4(), Email::fromString($emailString));

        $newEmail = 'weba@aso.maximo';

        $user->changeEmail(Email::fromString($newEmail));

        self::assertEquals($user->email(), $newEmail, 'Emails should be equals');

        $events = $user->getUncommittedEvents();

        self::assertCount(2, $events->getIterator(), '2 event should be in the buffer');

        /** @var DomainMessage $event */
        $event = $events->getIterator()->offsetGet(1);

        self::assertInstanceOf(UserEmailChanged::class, $event->getPayload(), 'Seccond event should be UserEmailChanged');
    }
}
