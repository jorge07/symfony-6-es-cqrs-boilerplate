<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Healthz;

use App\Shared\Infrastructure\Event\ReadModel\ElasticSearchEventRepository;
use App\User\Infrastructure\ReadModel\Mysql\MysqlReadModelUserRepository;
use UI\Http\Rest\Response\OpenApi;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class HealthzController
{
    public function __construct(private readonly ElasticSearchEventRepository $elasticSearchEventRepository, private readonly MysqlReadModelUserRepository $mysqlReadModelUserRepository)
    {
    }

    /**
     * @OA\Response(
     *     response=200,
     *     description="OK"
     * )
     * @OA\Response(
     *     response=500,
     *     description="Something not ok"
     * )
     * @OA\Tag(name="Healthz")
     */
    #[Route(path: '/healthz', name: 'healthz', methods: ['GET'])]
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
