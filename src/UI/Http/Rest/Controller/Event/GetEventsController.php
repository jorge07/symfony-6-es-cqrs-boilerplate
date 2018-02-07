<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\Event;

use App\Application\Query\Event\GetEvents\GetEventsQuery;
use App\UI\Http\Rest\Controller\QueryController;
use App\UI\Http\Rest\Response\Collection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetEventsController extends QueryController
{
    /**
     * @Route(
     *     name="events",
     *     path="/api/events"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $page  = $request->get('page', 1);
        $limit = $request->get('limit', 50);

        $query = new GetEventsQuery($page, $limit);

        $response = $this->ask($query);

        $collection = new Collection($page, $limit, $response['total'], $response['data']);

        return $this->jsonCollection($collection, true);
    }
}
