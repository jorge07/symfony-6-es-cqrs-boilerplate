<?php

declare(strict_types=1);


namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserChangeEmailController
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
        $command = new ChangeEmailCommand($uuid, $request->get('email'));

        $this->commandBus->handle($command);

        return JsonResponse::create();
    }

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @var CommandBus
     */
    private $commandBus;
}
