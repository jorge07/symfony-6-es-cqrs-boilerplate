<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use App\Shared\Application\Query\Item;
use App\User\Application\Query\User\FindByEmail\FindByEmailQuery;
use UI\Http\Rest\Controller\QueryController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class GetUserByEmailController extends QueryController
{
    /**
     * @Route(
     *     "/user/{email}",
     *     name="find_user",
     *     methods={"GET"}
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the user of the given email",
     *     ref="#/components/responses/users"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Not found"
     * )
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="email", type="string"),
     *     )
     * )
     *
     * @OA\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function __invoke(string $email): OpenApi
    {
        Assertion::email($email, "Email can\'t be empty or invalid");

        $command = new FindByEmailQuery($email);

        /** @var Item $user */
        $user = $this->ask($command);

        return $this->json($user);
    }
}
