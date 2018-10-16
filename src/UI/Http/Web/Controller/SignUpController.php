<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use App\Application\Command\User\SignUp\SignUpCommand;
use App\Domain\User\Exception\EmailAlreadyExistException;
use App\Domain\User\ValueObject\Uuid;
use Assert\Assertion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignUpController extends AbstractRenderController
{
    /**
     * @Route(
     *     "/sign-up",
     *     name="sign-up",
     *     methods={"GET"}
     * )
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(): Response
    {
        return $this->render('signup/index.html.twig');
    }

    /**
     * @Route(
     *     "/sign-up",
     *     name="sign-up-post",
     *     methods={"POST"}
     * )
     *
     * @throws \Assert\AssertionFailedException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    public function post(Request $request): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $uuid = Uuid::uuid4()->toString();

        try {
            Assertion::notNull($email, 'Email can\'t be null');
            Assertion::notNull($password, 'Password can\'t be null');

            $this->exec(new SignUpCommand($uuid, $email, $password));

            return $this->render('signup/user_created.html.twig', ['uuid' => $uuid, 'email' => $email]);
        } catch (EmailAlreadyExistException $exception) {
            return $this->render('signup/index.html.twig', ['error' => 'Email already exists.'], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $exception) {
            return $this->render('signup/index.html.twig', ['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
