<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Event;

use App\Shared\Application\Query\Collection;
use App\Shared\Application\Query\Event\GetEvents\GetEventsQuery;
use UI\Http\Rest\Controller\QueryController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
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
     * @OA\Response(
     *     response=200,
     *     description="Return events list",
     *     ref="#/components/responses/events"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request",
     *     @OA\JsonContent(ref="#/components/schemas/Error")
     *
     * )
     * @OA\Response(
     *     response=409,
     *     description="Conflict"
     * )
     *
     * @OA\Parameter(ref="#/components/parameters/page")
     * @OA\Parameter(ref="#/components/parameters/limit")
     *
     * @OA\Tag(name="Events")
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
