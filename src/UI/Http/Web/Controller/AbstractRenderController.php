<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use App\Infrastructure\Share\MessageBusHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Twig;

class AbstractRenderController
{
    /**
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     */
    protected function render(string $view, array $parameters = [], int $code = Response::HTTP_OK): Response
    {
        $content = $this->template->render($view, $parameters);

        return new Response($content, $code);
    }

    /**
     * @throws Throwable
     */
    protected function exec($command): void
    {
        MessageBusHelper::dispatchCommand($this->commandBus, $command);
    }

    /**
     * @throws Throwable
     */
    protected function ask($query)
    {
        return MessageBusHelper::dispatchQuery($this->queryBus, $query);
    }

    public function __construct(
        Twig\Environment $template,
        MessageBusInterface $commandBus,
        MessageBusInterface $queryBus
    ) {
        $this->template = $template;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    /**
     * @var MessageBusInterface
     */
    private $commandBus;

    /**
     * @var MessageBusInterface
     */
    private $queryBus;

    /**
     * @var Twig\Environment
     */
    private $template;
}
