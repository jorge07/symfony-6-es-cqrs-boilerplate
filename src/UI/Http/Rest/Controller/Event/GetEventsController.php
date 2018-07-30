<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\Event;

use App\Application\Query\Collection;
use App\Application\Query\Event\GetEvents\GetEventsQuery;
use App\UI\Http\Rest\Controller\QueryController;
use Assert\Assertion;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetEventsController extends QueryController
{
    /**
     * @Route(
     *     path="/events",
     *     name="events",
     *     methods={"GET"}
     * )
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 50);

        Assertion::numeric($page, 'Page number must be an integer');
        Assertion::numeric($limit, 'Limit results must be an integer');

        $query = new GetEventsQuery((int) $page, (int) $limit);

        /** @var Collection $response */
        $response = $this->ask($query);

        return $this->jsonCollection($response, true);
    }
}
