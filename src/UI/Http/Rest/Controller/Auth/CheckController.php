<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Auth;

use App\User\Application\Command\SignIn\SignInCommand;
use App\User\Application\Query\Auth\GetToken\GetTokenQuery;
use App\User\Domain\Exception\InvalidCredentialsException;
use UI\Http\Rest\Controller\CommandQueryController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class CheckController extends CommandQueryController
{
    /**
     * @Route(
     *     "/auth_check",
     *     name="auth_check",
     *     methods={"POST"},
     *     requirements={
     *      "_username": "\w+",
     *      "_password": "\w+"
     *     }
     * )
     * @OA\Response(
     *     response=200,
     *     description="Login success",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="token", type="string"
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Bad credentials"
     * )
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="_password", type="string"),
     *         @OA\Property(property="_username", type="string")
     *     )
     * )
     *
     * @OA\Tag(name="Auth")
     *
     * @throws AssertionFailedException
     * @throws InvalidCredentialsException
     * @throws Throwable
     */
    public function __invoke(Request $request): OpenApi
    {
        $username = $request->get('_username');

        Assertion::notNull($username, 'Username cant\'t be empty');

        $signInCommand = new SignInCommand(
            $username,
            $request->get('_password')
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
