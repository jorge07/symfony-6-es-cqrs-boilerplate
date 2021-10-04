<?php

declare(strict_types=1);

namespace UI\Http\Web\Controller;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Command\CommandInterface;
use App\Shared\Application\Query\Collection;
use App\Shared\Application\Query\Item;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Application\Query\QueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use Twig;

abstract class AbstractRenderController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private Twig\Environment $template
    ) {
    }

    protected function redirect(string $routeName, array $parameters = []): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate($routeName, $parameters)
        );
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

    /**
     * @return Item|Collection|mixed
     *
     * @throws Throwable
     */
    protected function ask(QueryInterface $query)
    {
        return $this->queryBus->ask($query);
    }
}
