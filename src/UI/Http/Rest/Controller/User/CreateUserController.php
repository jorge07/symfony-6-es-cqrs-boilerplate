<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\User\Create\CreateUserCommand;
use App\UI\Http\Rest\Controller\CommandController;
use Assert\Assertion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CreateUserController extends CommandController
{
    /**
     * @Route(
     *     "/api/users",
     *     name="user_create",
     *     methods={"POST"},
     *     requirements={
     *      "uuid": "\d+",
     *      "email": "\w+"
     * })
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $uuid = $request->get('uuid');
        $email = $request->get('email');
        $plainPassword = $request->get('password');

        Assertion::notNull($uuid, "Uuid can\'t be null");
        Assertion::notNull($email, "Email can\'t be null");
        Assertion::notNull($plainPassword, "Password can\'t be null");

        $commandRequest = new CreateUserCommand($uuid, $email, $plainPassword);

        $this->exec($commandRequest);

        return JsonResponse::create([], JsonResponse::HTTP_CREATED);
    }
}
