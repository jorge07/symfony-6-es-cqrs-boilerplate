<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use App\User\Application\Command\SignUp\SignUpCommand;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\CommandController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class SignUpController extends CommandController
{
    /**
     * @throws AssertionFailedException
     * @throws Throwable
     */
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'User created successfully',
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Bad request',
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Conflict',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'string'),
            ],
            type: 'object',
        )
    )]
    #[OA\Tag(name: 'User')]
    #[Route(path: '/signup', name: 'user_create', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): OpenApi
    {
        $uuid = (string) $request->request->get('uuid');
        $email = (string) $request->request->get('email');
        $plainPassword = (string) $request->request->get('password');

        Assertion::notEmpty($uuid, "Uuid can\'t be empty");
        Assertion::notEmpty($email, "Email can\'t be empty");
        Assertion::notEmpty($plainPassword, "Password can\'t be empty");

        $commandRequest = new SignUpCommand($uuid, $email, $plainPassword);

        $this->handle($commandRequest);

        return OpenApi::created("/user/$email");
    }
}
