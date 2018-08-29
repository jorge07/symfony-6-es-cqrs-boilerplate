<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class AbstractRenderController
{
    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $view, array $parameters = [], int $code = Response::HTTP_OK): Response
    {
        $content = $this->template->render($view, $parameters);

        return new Response($content, $code);
    }

    protected function exec($command): void
    {
        $this->commandBus->dispatch($command);
    }

    protected function ask($query)
    {
        return $this->queryBus->dispatch($query);
    }

    public function __construct(\Twig_Environment $template, MessageBusInterface $commandBus, MessageBusInterface $queryBus)
    {
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
     * @var \Twig_Environment
     */
    private $template;
}
