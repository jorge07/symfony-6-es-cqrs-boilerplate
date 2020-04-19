<?php

declare(strict_types=1);

namespace App\UI\Http\Web\Controller;

use App\Infrastructure\Share\Bus\CommandBus;
use App\Infrastructure\Share\Bus\CommandInterface;
use App\Infrastructure\Share\Bus\QueryBus;
use App\Infrastructure\Share\Bus\QueryInterface;
use Symfony\Component\HttpFoundation\Response;
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

    public function __construct(
        Twig\Environment $template,
        CommandBus $commandBus,
        QueryBus $queryBus
    ) {
        $this->template = $template;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    /** @var CommandBus */
    private $commandBus;

    /** @var QueryBus */
    private $queryBus;

    /** @var Twig\Environment */
    private $template;
}
