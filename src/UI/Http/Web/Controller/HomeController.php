<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use App\Application\Command\User\Create\CreateUserCommand;
use App\Application\Query\User\FindByEmail\FindByEmailQuery;
use App\Domain\Shared\Query\Exception\NotFoundException;
use Assert\Assertion;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractRenderController
{
    /**
     * @Route(
     *     "/",
     *     name="home",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function get(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
