<?php

declare(strict_types=1);


namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use App\UI\Http\Rest\Controller\CommandController;
use Assert\Assertion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserChangeEmailController extends CommandController
{
    /**
     * @Route(
     *     "/api/users/{uuid}/email",
     *     name="user_change_email",
     *     methods={"POST"}
     * )
     *
     * @param string $uuid
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        $email = $request->get('email');

        Assertion::notNull($email, "Email can\'t be null");

        $command = new ChangeEmailCommand($uuid, $email);

        $this->exec($command);

        return JsonResponse::create();
    }
}
