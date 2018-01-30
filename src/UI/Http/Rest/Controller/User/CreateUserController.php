<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\User\Create\CreateUserCommand;
use Assert\Assertion;
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

        Assertion::notNull($uuid, "Uuid can\'t be null");
        Assertion::notNull($email, "Email can\'t be null");

        $commandRequest = new CreateUserCommand($uuid, $email);

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
