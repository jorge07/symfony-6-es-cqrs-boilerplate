<?php

declare(strict_types=1);

namespace UI\Http\Web\Controller;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Query\QueryBusInterface;
use App\User\Infrastructure\Auth\Auth;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig;

abstract class AbstractSessionAwareController extends AbstractRenderController
{
    public function __construct(
        private Security $security,
        UrlGeneratorInterface $urlGenerator,
        Twig\Environment $template,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    ) {
        parent::__construct($urlGenerator, $commandBus, $queryBus, $template);
    }

    /**
     * @return Auth
     */
    protected function loggedUser(): UserInterface
    {
        return $this->security->getUser();
    }
}
