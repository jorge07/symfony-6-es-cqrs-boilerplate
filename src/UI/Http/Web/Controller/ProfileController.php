<?php

declare(strict_types=1);

namespace UI\Http\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ProfileController extends AbstractRenderController
{
    /**
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[Route(path: '/profile', name: 'profile', methods: ['GET'])]
    public function profile(): Response
    {
        return $this->render('profile/index.html.twig');
    }
}
