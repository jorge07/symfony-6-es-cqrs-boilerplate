<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use App\Shared\Application\Query\Item;
use App\User\Application\Query\User\FindByEmail\FindByEmailQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\QueryController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class GetUserByEmailController extends QueryController
{
    /**
     * @throws AssertionFailedException
     * @throws Throwable
     */
    #[OA\Response(
        ref: '#/components/responses/users',
        response: Response::HTTP_OK,
        description: 'Returns the user of the given email',
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Bad request',
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Not found',
    )]
    #[OA\Tag(name: 'User')]
    #[Security(name: 'Bearer')]
    #[Route(path: '/user/{email}', name: 'find_user', methods: [Request::METHOD_GET])]
    public function __invoke(string $email): OpenApi
    {
        Assertion::email($email, "Email can\'t be empty or invalid");

        $query = new FindByEmailQuery($email);

        /** @var Item $user */
        $user = $this->ask($query);

        return $this->json($user);
    }
}
