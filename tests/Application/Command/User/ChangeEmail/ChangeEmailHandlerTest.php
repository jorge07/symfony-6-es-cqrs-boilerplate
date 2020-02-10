<?php

declare(strict_types=1);

namespace App\Tests\Application\Command\User\ChangeEmail;

use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use App\Application\Command\User\SignUp\SignUpCommand;
use App\Domain\User\Event\UserEmailChanged;
use App\Tests\Application\ApplicationTestCase;
use App\Tests\Infrastructure\Share\Event\EventCollectorListener;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;

class ChangeEmailHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function update_user_email_should_command_should_fire_event(): void
    {
        $command = new SignUpCommand($uuid = Uuid::uuid4()->toString(), 'asd@asd.asd', 'password');

        $this->handle($command);

        $email = 'lol@asd.asd';

        $command = new ChangeEmailCommand($uuid, $email);

        $this->handle($command);

        /** @var EventCollectorListener $eventCollector */
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
