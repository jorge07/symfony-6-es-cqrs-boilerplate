<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\UI\Http\Rest\Response\JsonApiFormatter;
use League\Tactician\CommandBus;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommandQueryController extends QueryController
{
    protected function exec($command): void
    {
        $this->commandBus->handle($command);
    }

    public function __construct(CommandBus $commandBus, CommandBus $queryBus, JsonApiFormatter $formatter, UrlGeneratorInterface $router)
    {
        parent::__construct($queryBus, $formatter, $router);
        $this->commandBus = $commandBus;
    }

    /**
     * @var CommandBus
     */
    private $commandBus;
}
