<?php

declare(strict_types=1);

namespace Tests\App\Application\Command\User\ChangeEmail;


use App\User\Application\Command\ChangeEmail\ChangeEmailCommand;
use App\User\Application\Command\SignUp\SignUpCommand;
use App\User\Domain\Event\UserEmailChanged;
use Assert\AssertionFailedException;
use Broadway\Domain\DomainMessage;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\App\Shared\Application\ApplicationTestCase;
use Tests\App\Shared\Infrastructure\Event\EventCollectorListener;
use Throwable;

class ChangeEmailHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws Exception
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function update_user_email_should_command_should_fire_event(): void
    {
        $command = new SignUpCommand($uuid = Uuid::uuid4()->toString(), 'asd@asd.asd', 'password');

        $this->handle($command);

        $email = 'lol@asd.asd';

        $command = new ChangeEmailCommand($uuid, $email);

        $this->handle($command);

        /** @var EventCollectorListener $eventCollector
         */
        $eventCollector = $this->service(EventCollectorListener::class);

        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertCount(2, $events);

        /** @var UserEmailChanged $emailChangedEmail */
        $emailChangedEmail = $events[1]->getPayload();

        self::assertInstanceOf(UserEmailChanged::class, $emailChangedEmail);
        self::assertSame($email, $emailChangedEmail->email->toString());
    }
}
