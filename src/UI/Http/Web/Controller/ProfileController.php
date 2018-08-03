<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractRenderController
{
    /**
     * @Route(
     *     "/profile",
     *     name="profile",
     *     methods={"GET"}
     * )
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function profile(): Response
    {
        return $this->render('profile/index.html.twig');
    }
}
