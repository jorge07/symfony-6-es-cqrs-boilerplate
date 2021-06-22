<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Auth\Guard;

use App\User\Application\Command\SignIn\SignInCommand;
use App\User\Application\Query\Auth\GetAuthUserByEmail\GetAuthUserByEmailQuery;
use App\User\Domain\Exception\InvalidCredentialsException;
use App\Shared\Infrastructure\Bus\Command\MessengerCommandBus;
use App\Shared\Infrastructure\Bus\Query\MessengerQueryBus;
use Assert\AssertionFailedException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Throwable;

final class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    private const LOGIN = 'login';

    private const SUCCESS_REDIRECT = 'profile';

    private MessengerCommandBus $bus;

    private MessengerQueryBus $queryBus;

    private UrlGeneratorInterface $router;

    public function __construct(
        MessengerCommandBus $commandBus,
        MessengerQueryBus $queryBus,
        UrlGeneratorInterface $router
    ) {
        $this->bus = $commandBus;
        $this->router = $router;
        $this->queryBus = $queryBus;
    }

    private function getCredentials(Request $request): array
    {
        return [
            'email' => $request->request->get('_email'),
            'password' => $request->request->get('_password'),
        ];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate(self::SUCCESS_REDIRECT));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate(self::LOGIN);
    }

    /**
     * @param Request $request
     * @return PassportInterface
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function authenticate(Request $request): PassportInterface
    {
        $credentials = $this->getCredentials($request);

        try {
            $email = $credentials['email'];
            $plainPassword = $credentials['password'];

            $signInCommand = new SignInCommand($email, $plainPassword);

            $this->bus->handle($signInCommand);

            return new Passport(
                new UserBadge($email, function (string $email) {
                    return $this->queryBus->ask(new GetAuthUserByEmailQuery($email));
                }),
                new PasswordCredentials($plainPassword)
            );
        } catch (InvalidCredentialsException | InvalidArgumentException $exception) {
            throw new AuthenticationException();
        }
    }
}
