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
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[Route(path: '/sign-up', name: 'sign-up', methods: ['GET'])]
    public function get(): Response
    {
        $uuid = Uuid::uuid4()->toString();

        return $this->render('signup/index.html.twig', ['uuid' => $uuid]);
    }

    /**
     *
     * @throws AssertionFailedException
     * @throws Throwable
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[Route(path: '/sign-up', name: 'sign-up-post', methods: ['POST'])]
    public function post(Request $request): Response
    {
        $errorHTTPStatusCode = null;
        $afterErrorUuid = Uuid::uuid4()->toString();


        $uuid = $request->request->get('uuid');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        try {
            Assertion::notNull($uuid, 'Missing uuid');
            Assertion::notNull($email, 'Email can\'t be null');
            Assertion::notNull($password, 'Password can\'t be null');

            $this->handle(new SignUpCommand((string) $uuid, (string) $email, (string) $password));

            return $this->render('signup/user_created.html.twig', ['uuid' => $uuid, 'email' => $email]);
        } catch (EmailAlreadyExistException $exception) {
            $errorHTTPStatusCode = Response::HTTP_CONFLICT;

            return $this->render('signup/index.html.twig', ['uuid' => $afterErrorUuid, 'error' => $exception->getMessage()], $errorHTTPStatusCode);
        } catch (InvalidArgumentException $exception) {
            $errorHTTPStatusCode= Response::HTTP_BAD_REQUEST;

            return $this->render('signup/index.html.twig', ['uuid' => $afterErrorUuid, 'error' => $exception->getMessage()], $errorHTTPStatusCode);
        }
    }
}
