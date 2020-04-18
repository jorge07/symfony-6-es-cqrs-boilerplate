<?php

declare(strict_types=1);

namespace App\Tests\Domain\User;

use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Event\UserWasCreated;
use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\Specification\UniqueEmailSpecificationInterface;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use Broadway\Domain\DomainMessage;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserTest extends TestCase implements UniqueEmailSpecificationInterface
{
    /** @var bool */
    private $isUniqueException = false;

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function given_a_valid_email_it_should_create_a_user_instance(): void
    {
        $emailString = 'lol@aso.maximo';

        $user = User::create(
            Uuid::uuid4(),
            new Credentials(
                Email::fromString($emailString),
                HashedPassword::encode('password')
            ),
            $this
        );

        self::assertSame($emailString, $user->email());
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
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function given_a_new_email_it_should_change_if_not_eq_to_prev_email(): void
    {
        $emailString = 'lol@aso.maximo';

        $user = User::create(
            Uuid::uuid4(),
            new Credentials(
                Email::fromString($emailString),
                HashedPassword::encode('password')
            ),
            $this
        );

        $newEmail = 'weba@aso.maximo';

        $user->changeEmail(Email::fromString($newEmail), $this);

        self::assertSame($user->email(), $newEmail, 'Emails should be equals');

        $events = $user->getUncommittedEvents();

        self::assertCount(2, $events->getIterator(), '2 event should be in the buffer');

        /** @var DomainMessage $event */
        $event = $events->getIterator()->offsetGet(1);

        self::assertInstanceOf(UserEmailChanged::class, $event->getPayload(), 'Second event should be UserEmailChanged');
    }

    /**
     * @test
     *
     * @group unit
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function given_a_registered_email_it_should_throw_an_email_already_exists_exception(): void
    {
        self::expectException(EmailAlreadyExistException::class);

        $this->isUniqueException = true;

        $emailString = 'lol@aso.maximo';

        $user = User::create(
            Uuid::uuid4(),
            new Credentials(
                Email::fromString($emailString),
                HashedPassword::encode('password')
            ),
            $this
        );

        $newEmail = 'weba@aso.maximo';

        $user->changeEmail(Email::fromString($newEmail), $this);
    }

    /**
     * @throws EmailAlreadyExistException
     */
    public function isUnique(Email $email): bool
    {
        if ($this->isUniqueException) {
            throw new EmailAlreadyExistException();
        }

        return true;
    }

    /**
     * @test
     *
     * @group unit
     */
    public function given_a_new_email_when_email_changes_should_update_the_update_at_field(): void
    {
        $emailString = 'lol@aso.maximo';

        $user = User::create(
            Uuid::uuid4(),
            new Credentials(
                Email::fromString($emailString),
                HashedPassword::encode('password')
            ),
            $this
        );

        self::assertNotNull($user->createdAt());
        self::assertNull($user->updatedAt());

        $initialUpdatedAt = $user->updatedAt();
        \usleep(1000);
        $newEmail = 'weba@aso.maximo';
        $user->changeEmail(Email::fromString($newEmail), $this);

        self::assertNotSame($user->updatedAt(), $initialUpdatedAt);
    }
}
