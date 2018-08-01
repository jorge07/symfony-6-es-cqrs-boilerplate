<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use App\Domain\User\Auth\SessionInterface;
use App\Domain\User\Exception\ForbiddenException;
use App\UI\Http\Rest\Controller\CommandController;
use Assert\Assertion;
use League\Tactician\CommandBus;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserChangeEmailController extends CommandController
{
    /**
     * @Route(
     *     "/users/{uuid}/email",
     *     name="user_change_email",
     *     methods={"POST"}
     * )
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        $this->validateUuid($uuid);

        $email = $request->get('email');

        Assertion::notNull($email, "Email can\'t be null");

        $command = new ChangeEmailCommand($uuid, $email);

        $this->exec($command);

        return JsonResponse::create();
    }

    private function validateUuid(string $uuid): void
    {
        if (!$this->session->sameByUuid($uuid)) {
            throw new ForbiddenException();
        }
    }

    public function __construct(SessionInterface $session, CommandBus $commandBus)
    {
        parent::__construct($commandBus);
        $this->session = $session;
    }

    /**
     * @var SessionInterface
     */
    private $session;
}
