<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller\User;

use App\Domain\User\Event\UserEmailChanged;
use App\Tests\Infrastructure\Share\Event\EventCollectorListener;

use App\Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;

class ChangeEmailControllerTest extends JsonApiTestCase
{

    /**
     * @test
     *
     * @group e2e
     */
    public function given_a_valid_uuid_and_email_should_return_a_201_status_code()
    {
        $this->post('/api/users', [
            'uuid' => $uuid = Uuid::uuid4()->toString(),
            'email' => 'jo@jo.com',
            'password' => 'password'
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->post('/api/users/' . $uuid . '/email', [
            'email' => 'weba@jo.com'
        ]);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->client->getContainer()->get(EventCollectorListener::class);

        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertInstanceOf(UserEmailChanged::class, $events[0]->getPayload());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_a_invalid__email_should_return_a_400_status_code()
    {
        $this->post('/api/users', [
            'uuid' => $uuid = Uuid::uuid4()->toString(),
            'email' => 'jo@jo.com',
            'password' => 'password'
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->post('/api/users/' . $uuid . '/email', [
            'email' => 'webajo.com'
        ]);

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
