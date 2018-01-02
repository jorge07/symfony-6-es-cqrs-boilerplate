<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\User\Create\CreateUserCommand;
use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateUserController
{
    /**
     * @Route(
     *     "/api/users",
     *     name="user_create",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $commandRequest = new CreateUserCommand($request->get('uuid'), $request->get('email'));

        $this->commandBus->handle($commandRequest);

        return JsonResponse::create([], JsonResponse::HTTP_CREATED);
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
