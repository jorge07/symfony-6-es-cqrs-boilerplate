<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\Healthz;

use App\UI\Http\Rest\Controller\CommandQueryController;
use App\UI\Http\Rest\Response\OpenApi;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class HealthzController extends CommandQueryController
{
    /**
     * @Route(
     *     "/healthz",
     *     name="healthz",
     *     methods={"GET"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Something not ok"
     * )
     *
     * @SWG\Tag(name="Healthz")
     */
    public function __invoke(Request $request): OpenApi
    {
        return OpenApi::empty(200);
    }
}
