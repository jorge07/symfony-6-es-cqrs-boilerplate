<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Auth;

use App\User\Application\Command\SignIn\SignInCommand;
use App\User\Application\Query\Auth\GetToken\GetTokenQuery;
use App\User\Domain\Exception\InvalidCredentialsException;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\CommandQueryController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class CheckController extends CommandQueryController
{
    /**
     * @throws AssertionFailedException
     * @throws InvalidCredentialsException
     * @throws Throwable
     */
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Login success',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'token', type: 'string'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Bad request',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Bad credentials',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: '_password', type: 'string'),
                new OA\Property(property: '_username', type: 'string'),
            ],
            type: 'object'
        )
    )]
    #[OA\Tag(name: 'Auth')]
    #[Route(path: '/auth_check', name: 'auth_check', requirements: ['_username' => '\w+', '_password' => '\w+'], methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): OpenApi
    {
        $username = (string) $request->request->get('_username');

        Assertion::notEmpty($username, 'Username cant\'t be empty');

        $signInCommand = new SignInCommand(
            $username,
            (string) $request->request->get('_password')
        );

        $this->handle($signInCommand);

        return OpenApi::fromPayload(
            [
                'token' => $this->ask(new GetTokenQuery($username)),
            ],
            OpenApi::HTTP_OK
        );
    }
}
