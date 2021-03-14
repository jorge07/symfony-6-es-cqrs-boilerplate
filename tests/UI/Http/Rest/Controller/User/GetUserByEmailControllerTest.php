<?php

declare(strict_types=1);

namespace Tests\UI\Http\Rest\Controller\User;

use Tests\App\Shared\Infrastructure\Event\EventCollectorListener;
use Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Assert\AssertionFailedException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GetUserByEmailControllerTest extends JsonApiTestCase
{
    /**
     * @test
     *
     * @group e2e
     *
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function invalid_input_parameters_should_return_400_status_code(): void
    {
        $this->createUser();
        $this->auth();

        $this->get('/api/user/asd@');

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->cli->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = self::$container->get(EventCollectorListener::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }

    /**
     * @test
     *
     * @group e2e
     *
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function valid_input_parameters_should_return_404_status_code_when_not_exist(): void
    {
        $this->createUser();
        $this->auth();

        $this->get('/api/user/asd@asd.asd');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->cli->getResponse()->getStatusCode());

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = self::$container->get(EventCollectorListener::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }

    /**
     * @test
     *
     * @group e2e
     *
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function valid_input_parameters_should_return_200_status_code_when_exist(): void
    {
        $emailString = $this->createUser();
        $this->auth();

        $this->get('/api/user/' . $emailString);

        self::assertSame(Response::HTTP_OK, $this->cli->getResponse()->getStatusCode());

        $response = \json_decode($this->cli->getResponse()->getContent(), true);

        self::assertArrayHasKey('data', $response);
        self::assertArrayHasKey('id', $response['data']);
        self::assertArrayHasKey('type', $response['data']);
        self::assertArrayHasKey('attributes', $response['data']);
        self::assertArrayHasKey('uuid', $response['data']['attributes']);
        self::assertArrayHasKey('credentials.email', $response['data']['attributes']);
        self::assertArrayHasKey('createdAt', $response['data']['attributes']);
        self::assertEquals($emailString, $response['data']['attributes']['credentials.email']);

        /** @var EventCollectorListener $eventCollector */
        $eventCollector = self::$container->get(EventCollectorListener::class);

        $events = $eventCollector->popEvents();

        self::assertCount(0, $events);
    }
}
