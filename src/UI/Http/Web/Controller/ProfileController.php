<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractRenderController
{
    /**
     * @Route(
     *     "/profile",
     *     name="profile",
     *     methods={"GET"}
     * )
     */
    public function profile()
    {
        return $this->render('profile/index.html.twig');
    }
}
