<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\EventSubscriber;

use App\UI\Http\Rest\EventSubscriber\JsonBodyParserSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class JsonBodyParserSubscriberTest extends TestCase
{
    private JsonBodyParserSubscriber $jsonBodyParserSubscriber;

    public function setUp(): void
    {
        parent::setUp();

        $this->jsonBodyParserSubscriber = new JsonBodyParserSubscriber();
    }

    /**
     * @test
     *
     * @group unit
     */
    public function when_json_body_is_invalid(): void
    {
        $request = new Request([], [], [], [], [], [], '{"test":');
        $request->headers->set('Content-Type', 'application/json');

        $requestEvent = new RequestEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->jsonBodyParserSubscriber->onKernelRequest($requestEvent);

        $response = $requestEvent->getResponse();

        self::assertEquals('Unable to parse json request.', $response->getContent());
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
