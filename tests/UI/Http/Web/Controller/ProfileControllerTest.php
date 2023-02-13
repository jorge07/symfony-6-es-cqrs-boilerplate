<?php

declare(strict_types=1);

namespace Tests\App\UI\Http\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends WebTestCase
{
    /**
     * @test
     *
     * @group e2e
     */
    public function anon_user_should_be_redirected_to_sign_in(): void
    {
        $client = self::createClient();

        $client->request('GET', '/profile');

        /** @var RedirectResponse $response */
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertStringContainsString('/sign-in', $response->getTargetUrl());
    }
}
