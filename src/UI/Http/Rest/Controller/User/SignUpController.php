<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use App\User\Application\Command\SignUp\SignUpCommand;
use UI\Http\Rest\Controller\CommandController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class SignUpController extends CommandController
{
    /**
     *
     * @OA\Response(
     *     response=201,
     *     description="User created successfully"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @OA\Response(
     *     response=409,
     *     description="Conflict"
     * )
     * @OA\RequestBody(
     *     @OA\Schema(type="object"),
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="uuid", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="password", type="string")
     *     )
     * )
     *
     * @OA\Tag(name="User")
     *
     * @throws AssertionFailedException
     * @throws Throwable
     */
    #[Route(path: '/signup', name: 'user_create', methods: ['POST'])]
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
