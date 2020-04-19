<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Controller;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Infrastructure\Share\Bus\CommandBus;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class JsonApiTestCase extends WebTestCase
{
    public const DEFAULT_EMAIL = 'lol@lo.com';

    public const DEFAULT_PASS = '1234567890';

    protected ?KernelBrowser $cli;

    private ?string $token = null;

    protected ?UuidInterface $userUuid;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->cli = static::createClient();
    }

    /**
     * @throws \Assert\AssertionFailedException
     * @throws \Exception
     */
    protected function createUser(string $email = self::DEFAULT_EMAIL, string $password = self::DEFAULT_PASS): string
    {
        $this->userUuid = Uuid::uuid4();

        $signUp = new SignUpCommand(
            $this->userUuid->toString(),
            $email,
            $password
        );

        /** @var CommandBus $commandBus */
        $commandBus = $this->cli->getContainer()->get(CommandBus::class);

        $commandBus->handle($signUp);

        return $email;
    }

    protected function post(string $uri, array $params): void
    {
        $this->cli->request(
            'POST',
            $uri,
            [],
            [],
            $this->headers(),
            (string) \json_encode($params)
        );
    }

    protected function get(string $uri, array $parameters = []): void
    {
        $this->cli->request(
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

        /** @var string $content */
        $content = $this->cli->getResponse()->getContent();

        $response = \json_decode($content, true);

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

    protected function tearDown(): void
    {
        $this->cli = null;
        $this->token = null;
        $this->userUuid = null;
    }
}
