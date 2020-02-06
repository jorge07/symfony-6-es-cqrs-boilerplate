<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractRenderController
{
    /**
     * @Route(
     *     "/",
     *     name="home",
     *     methods={"GET"}
     * )
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function get(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
