<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\User\Query\UserRead;
use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetUserByEmailController
{
    /**
     * @Route(
     *     "/api/users",
     *     name="find_user",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $command = new FindByEmailQuery($request->get('email'));

        /** @var UserRead $user */
        $user = $this->queryBus->handle($command);

        return JsonResponse::create([ 'user' => $user->serialize() ]);
    }

    public function __construct(CommandBus $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    /**
     * @var CommandBus
     */
    private $queryBus;
}
