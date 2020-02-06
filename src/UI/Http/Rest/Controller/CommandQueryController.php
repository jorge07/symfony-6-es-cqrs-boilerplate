<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\Infrastructure\Share\MessageBusHelper;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class CommandQueryController extends QueryController
{
    /**
     * @throws Throwable
     */
    protected function exec($command): void
    {
        MessageBusHelper::dispatchCommand($this->commandBus, $command);
    }

    public function __construct(
        MessageBusInterface $commandBus,
        MessageBusInterface $queryBus,
        JsonApiFormatter $formatter,
        UrlGeneratorInterface $router
    ) {
        parent::__construct($queryBus, $formatter, $router);
        $this->commandBus = $commandBus;
    }

    /**
     * @var MessageBusInterface
     */
    private $commandBus;
}
