<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Healthz;

use App\Shared\Infrastructure\Event\ReadModel\ElasticSearchEventRepository;
use App\User\Infrastructure\ReadModel\Mysql\MysqlReadModelUserRepository;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Response\OpenApi;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class HealthzController
{
    public function __construct(
        private readonly ElasticSearchEventRepository $elasticSearchEventRepository,
        private readonly MysqlReadModelUserRepository $mysqlReadModelUserRepository
    ){
    }

    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK'
    )]
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Something not ok'
    )]
    #[OA\Tag(name: 'Healthz')]
    #[Route(path: '/healthz', name: 'healthz', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): OpenApi
    {
        $elastic = null;
        $mysql = null;

        if (
            true === $elastic = $this->elasticSearchEventRepository->isHealthly() &&
            true === $mysql = $this->mysqlReadModelUserRepository->isHealthy()
        ) {
            return OpenApi::empty(200);
        }

        return OpenApi::fromPayload(
            [
                'Healthy services' => [
                    'Elastic' => $elastic,
                    'MySQL' => $mysql,
                ],
            ],
            500
        );
    }
}
