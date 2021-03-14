<?php

declare(strict_types=1);

namespace Tests\UI\Http\Rest\Controller;

use App\Shared\Infrastructure\Bus\Command\MessengerCommandBus;
use App\User\Application\Command\SignUp\SignUpCommand;
use Assert\AssertionFailedException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

abstract class JsonApiTestCase extends WebTestCase
{
    public const DEFAULT_EMAIL = 'lol@lo.com';

    public const DEFAULT_PASS = '1234567890';

    protected ?KernelBrowser $cli;

    private ?string $token = null;

    protected ?UuidInterface $userUuid;

    protected function setUp(): void
    {
        $this->cli = static::createClient();
    }

    /**
     * @throws AssertionFailedException
     * @throws Throwable
     */
    protected function createUser(string $email = self::DEFAULT_EMAIL, string $password = self::DEFAULT_PASS): string
    {
        $this->userUuid = Uuid::uuid4();

        $signUp = new SignUpCommand(
            $this->userUuid->toString(),
            $email,
            $password
        );

        /** @var MessengerCommandBus $commandBus */
        $commandBus = self::$container->get(MessengerCommandBus::class);

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

    protected function fireTerminateEvent(): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->cli->getContainer()->get('event_dispatcher');

        $dispatcher->dispatch(
            new TerminateEvent(
                static::$kernel,
                Request::create('/'),
                new Response()
            ),
            KernelEvents::TERMINATE
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cli = null;
        $this->token = null;
        $this->userUuid = null;
    }
}
