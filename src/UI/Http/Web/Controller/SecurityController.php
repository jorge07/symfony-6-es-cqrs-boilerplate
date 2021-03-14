<?php

declare(strict_types=1);

namespace UI\Http\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SecurityController extends AbstractRenderController
{
    /**
     * @Route(
     *     "/sign-in",
     *     name="login",
     *     methods={"GET", "POST"}
     * )
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function login(AuthenticationUtils $authUtils): Response
    {
        return $this->render('signin/login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route(
     *     "/logout",
     *     name="logout"
     * )
     */
    public function logout(): void
    {
        throw new AuthenticationException('I shouldn\'t be here..');
    }
}
