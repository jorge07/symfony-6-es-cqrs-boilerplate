<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller\User;

use App\Domain\User\Event\UserEmailChanged;
use App\Tests\Infrastructure\Share\Event\EventCollectorListener;
use App\Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Broadway\Domain\DomainMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class ChangeEmailControllerTest extends JsonApiTestCase
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
        $this->post('/api/users/' . $this->userUuid->toString() . '/email', [
            'email' => 'weba@jo.com',
        ]);

        self::assertSame(Response::HTTP_OK, $this->cli->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->cli->getContainer()->get(EventCollectorListener::class);

        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertInstanceOf(UserEmailChanged::class, $events[0]->getPayload());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function given_a_valid_uuid_and_email_user_should_not_change_others_email_and_gets_401(): void
    {
        $this->post('/api/users/' . Uuid::uuid4()->toString() . '/email', [
            'email' => 'weba@jo.com',
        ]);

        self::assertSame(Response::HTTP_FORBIDDEN, $this->cli->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = $this->cli->getContainer()->get(EventCollectorListener::class);

        /** @var DomainMessage[] $events */
        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }

    /**
     * @test
     *
     * @group e2e
     *
     * @throws \Exception
     */
    public function given_a_invalid__email_should_return_a_400_status_code(): void
    {
        $this->post('/api/users/' . $this->userUuid->toString() . '/email', [
            'email' => 'webajo.com',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->cli->getResponse()->getStatusCode());
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createUser();
        $this->auth();
    }
}
