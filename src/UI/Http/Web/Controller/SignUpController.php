<?php

declare(strict_types=1);

namespace UI\Http\Web\Controller;

use App\User\Application\Command\SignUp\SignUpCommand;
use App\User\Domain\Exception\EmailAlreadyExistException;
use Assert\Assertion;
use Assert\AssertionFailedException;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SignUpController extends AbstractRenderController
{
    /**
     * @Route(
     *     "/sign-up",
     *     name="sign-up",
     *     methods={"GET"}
     * )
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
     * @throws AssertionFailedException
     * @throws Throwable
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function post(Request $request): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $uuid = Uuid::uuid4()->toString();

        try {
            Assertion::notNull($email, 'Email can\'t be null');
            Assertion::notNull($password, 'Password can\'t be null');

            $this->handle(new SignUpCommand($uuid, $email, $password));

            return $this->render('signup/user_created.html.twig', ['uuid' => $uuid, 'email' => $email]);
        } catch (EmailAlreadyExistException $exception) {
            return $this->render('signup/index.html.twig', ['error' => $exception->getMessage()], Response::HTTP_CONFLICT);
        } catch (InvalidArgumentException $exception) {
            return $this->render('signup/index.html.twig', ['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
