<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Command\CommandInterface;
use App\Shared\Application\Query\QueryBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

abstract class CommandQueryController extends QueryController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        UrlGeneratorInterface $router
    ) {
        parent::__construct($queryBus, $router);
    }

    /**
     * @throws Throwable
     */
    protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}
