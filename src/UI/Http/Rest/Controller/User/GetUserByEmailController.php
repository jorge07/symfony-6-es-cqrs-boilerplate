<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\User\Query\UserView;
use App\UI\Http\Rest\Controller\QueryController;
use Assert\Assertion;
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
        $email = $request->get('email');
        Assertion::notNull($email, "Email can\'t be null");

        $command = new FindByEmailQuery($email);

        /** @var UserView $user */
        $user = $this->ask($command);

        return $this->json($user);
    }
}
