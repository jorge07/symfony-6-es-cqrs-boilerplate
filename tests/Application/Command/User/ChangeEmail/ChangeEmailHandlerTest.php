<?php

declare(strict_types=1);

namespace App\Tests\Application\Command\User\ChangeEmail;

use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use App\Application\Command\User\Create\CreateUserCommand;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\User\Event\UserEmailChanged;
use App\Domain\User\Query\UserView;
use App\Tests\Application\Command\ApplicationTestCase;
use App\Tests\Infrastructure\Share\Bus\EventCollectorMiddleware;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChangeEmailHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function update_user_email_should_command_should_fire_event()
    {
        $command = new CreateUserCommand($uuid = Uuid::uuid4()->toString(), 'asd@asd.asd');

        $this
            ->handle($command);

        $email = 'lol@asd.asd';

        $command = new ChangeEmailCommand($uuid, $email);

        $this
            ->handle($command);

        /** @var EventCollectorMiddleware $eventCollector */
        $eventCollector = $this->service(EventCollectorMiddleware::class);

        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertCount(2, $events);

        /** @var UserEmailChanged $emailChangedEmail */
        $emailChangedEmail = $events[1]->getPayload();

        self::assertInstanceOf(UserEmailChanged::class, $emailChangedEmail);
        self::assertEquals($email, $emailChangedEmail->email->toString());
    }
}
