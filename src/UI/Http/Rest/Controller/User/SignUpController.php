<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\UI\Http\Rest\Controller\CommandController;
use Assert\Assertion;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SignUpController extends CommandController
{
    /**
     * @Route(
     *     "/users",
     *     name="user_create",
     *     methods={"POST"},
     *     requirements={
     *      "uuid": "\d+",
     *      "email": "\w+"
     * })
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $uuid = $request->get('uuid');
        $email = $request->get('email');
        $plainPassword = $request->get('password');

        Assertion::notNull($uuid, "Uuid can\'t be null");
        Assertion::notNull($email, "Email can\'t be null");
        Assertion::notNull($plainPassword, "Password can\'t be null");

        $commandRequest = new SignUpCommand($uuid, $email, $plainPassword);

        $this->exec($commandRequest);

        return JsonResponse::create(null, JsonResponse::HTTP_CREATED);
    }
}
