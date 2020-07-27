<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\Event;

use App\Application\Query\Collection;
use App\Application\Query\Event\GetEvents\GetEventsQuery;
use App\UI\Http\Rest\Controller\QueryController;
use App\UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class GetEventsController extends QueryController
{
    /**
     * @Route(
     *     path="/events",
     *     name="events",
     *     methods={"GET"}
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Return events list"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @SWG\Response(
     *     response=409,
     *     description="Conflict"
     * )
     * @SWG\Parameter(
     *     name="page",
     *     type="integer",
     *     in="path"
     * )
     * @SWG\Parameter(
     *     name="limit",
     *     type="integer",
     *     in="path"
     * )
     *
     * @SWG\Tag(name="Events")
     *
     * @Security(name="Bearer")
     *
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function __invoke(Request $request): OpenApi
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 50);

        Assertion::numeric($page, 'Page number must be an integer');
        Assertion::numeric($limit, 'Limit results must be an integer');

        $query = new GetEventsQuery((int) $page, (int) $limit);

        /** @var Collection $response */
        $response = $this->ask($query);

        return $this->jsonCollection($response, 200, true);
    }
}
