<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use App\Infrastructure\Share\Bus\Command\CommandBus;
use App\Infrastructure\Share\Bus\Command\CommandInterface;
use App\Infrastructure\Share\Bus\Query\QueryBus;
use App\Infrastructure\Share\Bus\Query\QueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Twig;

abstract class AbstractRenderController
{
    private CommandBus $commandBus;

    private QueryBus $queryBus;

    private Twig\Environment $template;

    public function __construct(
        Twig\Environment $template,
        CommandBus $commandBus,
        QueryBus $queryBus
    ) {
        $this->template = $template;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

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
    protected function exec(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }

    /**
     * @return mixed
     *
     * @throws Throwable
     */
    protected function ask(QueryInterface $query)
    {
        return $this->queryBus->handle($query);
    }
}
