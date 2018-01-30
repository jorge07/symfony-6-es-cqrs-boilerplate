<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\User\Query\UserView;
use App\UI\Http\Rest\Controller\QueryController;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetUserByEmailController extends QueryController
{
    /**
     * @Route(
     *     "/api/user/{email}",
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

        /** @var UserView $user */
        $user = $this->ask($command);

        return $this->json($user);
    }

}
