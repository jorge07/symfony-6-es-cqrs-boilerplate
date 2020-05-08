<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Infrastructure\Share\Bus\Query\Item;
use App\UI\Http\Rest\Controller\QueryController;
use Assert\Assertion;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class GetUserByEmailController extends QueryController
{
    /**
     * @Route(
     *     "/user/{email}",
     *     name="find_user",
     *     methods={"GET"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the user of the given email"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not found"
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="path",
     *     type="string",
     *     description="email"
     * )
     *
     * @SWG\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @throws \Assert\AssertionFailedException
     * @throws \Throwable
     */
    public function __invoke(string $email): JsonResponse
    {
        Assertion::notNull($email, "Email can\'t be null");

        $command = new FindByEmailQuery($email);

        /** @var Item $user */
        $user = $this->ask($command);

        return $this->json($user);
    }
}
