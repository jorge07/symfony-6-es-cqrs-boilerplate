<?php

namespace App\Tests\UI\Http\Rest\Controller\User;

use App\Domain\User\Event\UserWasCreated;
use App\Infrastructure\Share\Event\EventCollectorHandler;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;

class CreateUserControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    private function request(array $params)
    {
        $this->client->request(
            'POST',
            '/api/users',
            $params
        );
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_a_valid_uuid_and_email_should_return_a_201_status_code()
    {

        $this->request([
            'uuid' => Uuid::uuid4()->toString(),
            'email' => 'jo@jo.com'
        ]);


        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorHandler $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorHandler::class);

        $events = $eventCollector->popEvents();

        self::assertCount(1, $events);

        /** @var DomainMessage $userWasCreatedEvent */
        $userWasCreatedEvent = $events[0];

        self::assertInstanceOf(UserWasCreated::class, $userWasCreatedEvent->getPayload());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function invalid_input_parameters_should_return_400_status_code()
    {
        $this->request(
            [
                'uuid' => Uuid::uuid4()->toString(),
                'email' => 'invalid email'
            ]
        );

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorHandler $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorHandler::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }
}
