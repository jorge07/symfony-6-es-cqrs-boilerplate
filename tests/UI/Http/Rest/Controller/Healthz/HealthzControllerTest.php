<?php

declare(strict_types=1);

namespace Tests\UI\Http\Rest\Controller\Healthz;

use Tests\UI\Http\Rest\Controller\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class HealthzControllerTest extends JsonApiTestCase
{
    /**
     * @test
     *
     * @group e2e
     */
    public function events_list_must_return_404_when_no_page_found(): void
    {
        $this->get('/api/healthz');

        self::assertSame(Response::HTTP_OK, $this->cli->getResponse()->getStatusCode());
    }
}
