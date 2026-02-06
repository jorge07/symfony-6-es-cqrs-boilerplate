<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use App\Shared\Application\Command\CommandBusInterface;
use App\User\Application\Command\ChangeEmail\ChangeEmailCommand;
use App\User\Domain\Exception\ForbiddenException;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\CommandController;
use UI\Http\Session;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class UserChangeEmailController extends CommandController
{
    public function __construct(private readonly Session $session, CommandBusInterface $commandBus)
    {
        parent::__construct($commandBus);
    }

    /**
     * @throws AssertionFailedException
     * @throws Throwable
     */
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Email changed'
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Bad request'
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Conflict',
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email')
            ],
            type: 'object'
        )
    )]
    #[OA\Parameter(
        name: 'uuid',
        in: 'path',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Tag(name: 'User')]
    #[Security(name: 'Bearer')]
    #[Route(path: '/users/{uuid}/email', name: 'user_change_email', methods: [Request::METHOD_POST])]
    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        $this->validateUuid($uuid);

        $email = (string) $request->request->get('email');

        Assertion::notEmpty($email, "Email can\'t be empty");

        $command = new ChangeEmailCommand($uuid, $email);

        $this->handle($command);

        return new JsonResponse();
    }

    private function validateUuid(string $uuid): void
    {
        if (!$this->session->get()->uuid()->equals(Uuid::fromString($uuid))) {
            throw new ForbiddenException();
        }
    }
}
