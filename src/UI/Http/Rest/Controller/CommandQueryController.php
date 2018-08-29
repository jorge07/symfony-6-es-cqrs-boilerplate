<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\UI\Http\Rest\Response\JsonApiFormatter;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommandQueryController extends QueryController
{
    protected function exec($command): void
    {
        $this->commandBus->dispatch($command);
    }

    public function __construct(MessageBusInterface $commandBus, MessageBusInterface $queryBus, JsonApiFormatter $formatter, UrlGeneratorInterface $router)
    {
        parent::__construct($queryBus, $formatter, $router);
        $this->commandBus = $commandBus;
    }

    /**
     * @var MessageBusInterface
     */
    private $commandBus;
}
