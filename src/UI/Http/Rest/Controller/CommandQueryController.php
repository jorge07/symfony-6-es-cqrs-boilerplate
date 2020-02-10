<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Infrastructure\Share\Bus\CommandBus;
use App\Infrastructure\Share\Bus\CommandInterface;
use App\Infrastructure\Share\Bus\QueryBus;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommandQueryController extends QueryController
{
    /**
     * @throws \Throwable
     */
    protected function exec(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }

    public function __construct(
        CommandBus $commandBus,
        QueryBus $queryBus,
        JsonApiFormatter $formatter,
        UrlGeneratorInterface $router
    ) {
        parent::__construct($queryBus, $formatter, $router);
        $this->commandBus = $commandBus;
    }

    /** @var CommandBus */
    private $commandBus;
}
