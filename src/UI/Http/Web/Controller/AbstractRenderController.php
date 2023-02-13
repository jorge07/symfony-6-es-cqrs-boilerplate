<?php

declare(strict_types=1);

namespace UI\Http\Web\Controller;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Command\CommandInterface;
use App\Shared\Application\Query\Collection;
use App\Shared\Application\Query\Item;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Application\Query\QueryInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Twig;

abstract class AbstractRenderController
{
    public function __construct(private readonly Twig\Environment $template, private readonly CommandBusInterface $commandBus, private readonly QueryBusInterface $queryBus)
    {
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
    protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }

    /**\
     * @throws Throwable
     */
    protected function ask(QueryInterface $query): mixed
    {
        return $this->queryBus->ask($query);
    }
}
