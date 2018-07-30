<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller;

use App\Application\Command\User\SignUp\SignUpCommand;
use League\Tactician\CommandBus;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class JsonApiTestCase extends WebTestCase
{
    public const DEFAULT_EMAIL = 'lol@lo.com';

    public const DEFAULT_PASS = '1234567890';

    protected function createUser(string $email = self::DEFAULT_EMAIL, string $password = self::DEFAULT_PASS): string
    {
        $this->userUuid = Uuid::uuid4();

        $signUp = new SignUpCommand(
            $this->userUuid->toString(),
            $email,
            $password
        );

        /** @var CommandBus $commandBus */
        $commandBus = $this->client->getContainer()->get('tactician.commandbus.command');

        $commandBus->handle($signUp);

        return $email;
    }

    protected function post(string $uri, array $params)
    {
        $this->client->request(
            'POST',
            $uri,
            [],
            [],
            $this->headers(),
            json_encode($params)
        );
    }

    protected function get(string $uri, array $parameters = [])
    {
        $this->client->request(
            'GET',
            $uri,
            $parameters,
            [],
            $this->headers()
        );
    }

    protected function auth(string $username = self::DEFAULT_EMAIL, string $password = self::DEFAULT_PASS): void
    {
        $this->post('/api/auth_check', [
            '_username' => $username ?: self::DEFAULT_EMAIL,
            '_password' => $password ?: self::DEFAULT_PASS,
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->token = $response['token'];
    }

    protected function logout(): void
    {
        $this->token = null;
    }

    private function headers(): array
    {
        $headers = [
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($this->token) {
            $headers['HTTP_Authorization'] = 'Bearer ' . $this->token;
        }

        return $headers;
    }

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->token = null;
        $this->userUuid = null;
    }

    /** @var Client|null */
    protected $client;

    /** @var string|null */
    private $token;

    /** @var UuidInterface|null */
    protected $userUuid;
}
