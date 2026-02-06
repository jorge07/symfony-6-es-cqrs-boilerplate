<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Event;

use App\Shared\Application\Query\Collection;
use App\Shared\Application\Query\Event\GetEvents\GetEventsQuery;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\QueryController;
use UI\Http\Rest\Response\OpenApi;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class GetEventsController extends QueryController
{
    /**
     * @throws AssertionFailedException
     * @throws Throwable
     */
    #[OA\Response(
        ref: '#/components/responses/events',
        response: Response::HTTP_OK,
        description: 'Return events list'
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Bad request',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Conflict'
    )]
    #[OA\Parameter(ref: '#/components/parameters/page')]
    #[OA\Parameter(ref: '#/components/parameters/limit')]
    #[OA\Tag(name: 'Events')]
    #[Security(name: 'Bearer')]
    #[Route(path: '/events', name: 'events', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): OpenApi
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 50);

        Assertion::numeric($page, 'Page number must be an integer');
        Assertion::numeric($limit, 'Limit results must be an integer');

        $query = new GetEventsQuery((int) $page, (int) $limit);

        /** @var Collection $response */
        $response = $this->ask($query);

        return $this->jsonCollection($response, 200, true);
    }
}
