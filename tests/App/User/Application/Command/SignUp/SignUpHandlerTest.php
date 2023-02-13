<?php

declare(strict_types=1);

namespace Tests\App\Application\Command\User\SignUp;

use App\User\Application\Command\SignUp\SignUpCommand;
use App\User\Domain\Event\UserWasCreated;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use Tests\App\Shared\Application\ApplicationTestCase;
use Tests\App\Shared\Infrastructure\Event\EventCollectorListener;

class SignUpHandlerTest extends ApplicationTestCase
{
    /**
     * @test
     *
     * @group integration
     *
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public function command_handler_must_fire_domain_event(): void
    {
        $uuid = Uuid::uuid4();
        $email = 'asd@asd.asd';

        $command = new SignUpCommand($uuid->toString(), $email, 'password');
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
