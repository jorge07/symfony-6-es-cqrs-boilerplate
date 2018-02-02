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

    /**
     * @Route(
     *     "/",
     *     name="home_post",
     *     methods={"POST"}
     * )
     *
     * @return Response
     */
    public function post(Request $request): Response
    {
        $email = $request->request->get('email');
        $uuid = Uuid::uuid4()->toString();
        try {

            Assertion::notNull($email, 'Email can\'t be null');

            $this->ask(new FindByEmailQuery($email));

            return $this->render('home/index.html.twig', ['error' => 'Email already exists.'], 409);

        } catch (NotFoundException $exception) {

            $this->exec(new CreateUserCommand($uuid, $email));

            return $this->render('home/user_created.html.twig', ['uuid' => $uuid, 'email' => $email]);

        }catch (\InvalidArgumentException $exception) {

            return $this->render('home/index.html.twig', ['error' => $exception->getMessage()], 400);
        }
    }
}
