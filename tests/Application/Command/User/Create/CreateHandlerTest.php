<?php

namespace App\Tests\Application\Command\User\Create;

use App\Application\Command\User\Create\CreateUserCommand;
use App\Domain\User\Event\UserWasCreated;
use App\Tests\Application\Command\ApplicationTestCase;
use App\Tests\Infrastructure\Share\Event\EventCollectorListener;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;

class CreateHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     */
    public function command_handler_must_fire_domain_event()
    {
        $uuid = Uuid::uuid4();
        $email = 'asd@asd.asd';

        $command = new CreateUserCommand($uuid->toString(), $email);
        $this
            ->handle($command);

        /** @var EventCollectorListener $collector */
        $collector = $this->service(EventCollectorListener::class);

        /** @var DomainMessage[] $events */
        $events = $collector->popEvents();

        self::assertCount(1, $events);

        /** @var UserWasCreated $userCreatedEvent */
        $userCreatedEvent = $events[0]->getPayload();

        self::assertInstanceOf(UserWasCreated::class, $userCreatedEvent);
    }
}
