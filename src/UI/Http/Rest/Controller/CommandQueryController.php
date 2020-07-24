<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Infrastructure\Share\Bus\Command\CommandBus;
use App\Infrastructure\Share\Bus\Command\CommandInterface;
use App\Infrastructure\Share\Bus\Query\QueryBus;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

abstract class CommandQueryController extends QueryController
{
    private CommandBus $commandBus;

    public function __construct(
        CommandBus $commandBus,
        QueryBus $queryBus,
        UrlGeneratorInterface $router
    ) {
        parent::__construct($queryBus, $router);
        $this->commandBus = $commandBus;
    }

    /**
     * @throws Throwable
     */
    protected function exec(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}
