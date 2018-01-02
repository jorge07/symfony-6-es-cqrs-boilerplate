<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller\User;

use App\Domain\User\Event\UserEmailChanged;
use App\Infrastructure\Share\Event\EventCollectorHandler;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;

class ChangeEmailControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    private function request(string $uri, array $params)
    {
        $this->client->request(
            'POST',
            $uri,
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
        $this->request('/api/users', [
            'uuid' => $uuid = Uuid::uuid4()->toString(),
            'email' => 'jo@jo.com'
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->request('/api/users/'.$uuid.'/email', [
            'email' => 'weba@jo.com'
        ]);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorHandler $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorHandler::class);

        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertCount(1, $events);

        $userEmailChangedEvent = $events[0];

        self::assertInstanceOf(UserEmailChanged::class, $userEmailChangedEvent->getPayload());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_a_invalid__email_should_return_a_400_status_code()
    {
        $this->request('/api/users', [
            'uuid' => $uuid = Uuid::uuid4()->toString(),
            'email' => 'jo@jo.com'
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->request('/api/users/'.$uuid.'/email', [
            'email' => 'webajo.com'
        ]);

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
