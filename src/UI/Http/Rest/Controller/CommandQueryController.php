<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Application\Command\CommandBusInterface;
use App\Application\Command\CommandInterface;
use App\Application\Query\QueryBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

abstract class CommandQueryController extends QueryController
{
    private CommandBusInterface $commandBus;

    public function __construct(
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        UrlGeneratorInterface $router
    ) {
        parent::__construct($queryBus, $router);
        $this->commandBus = $commandBus;
    }

    /**
     * @throws Throwable
     */
    protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}
