<?php

namespace App\Tests\UI\Http\Rest\Controller\User;

use App\Domain\User\Event\UserWasCreated;
use App\Tests\Infrastructure\Share\Event\EventCollectorListener;
use App\Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class SignUpControllerTest extends JsonApiTestCase
{
    /**
     * @test
     *
     * @group e2e
     *
     * @throws \Exception
     */
    public function given_a_valid_uuid_and_email_should_return_a_201_status_code(): void
    {
        $this->post('/api/users', [
            'uuid'     => Uuid::uuid4()->toString(),
            'email'    => 'jo@jo.com',
            'password' => 'oaisudaosudoaudo',
        ]);

        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorListener::class);

        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertCount(1, $events);

        $userWasCreatedEvent = $events[0];

        self::assertInstanceOf(UserWasCreated::class, $userWasCreatedEvent->getPayload());
    }

    /**
     * @test
     *
     * @group e2e
     *
     * @throws \Exception
     */
    public function invalid_input_parameters_should_return_400_status_code(): void
    {
        $this->post('/api/users', [
            'uuid'  => Uuid::uuid4()->toString(),
            'email' => 'invalid email',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorListener::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createUser();
        $this->auth();
    }
}
