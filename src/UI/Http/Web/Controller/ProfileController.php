<?php

declare(strict_types=1);

namespace UI\Http\Web\Controller;

use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Infrastructure\Form\FormException;
use App\Task\Application\Command\Create\CreateTaskCommand;
use App\Task\Application\Command\Update\UpdateTaskCommand;
use App\Task\Application\Query\FindAll\FindAllQuery;
use App\Task\Application\Query\FindOneByUuid\FindOneByUuidQuery;
use App\Task\Infrastructure\TaskFormFactory;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Throwable;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ProfileController extends AbstractSessionAwareController
{
    public function __construct(
        private TaskFormFactory $taskFormFactory,
        UrlGeneratorInterface $urlGenerator,
        Security $security,
        Twig\Environment $template,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    )
    {
        parent::__construct($security, $urlGenerator, $template, $commandBus, $queryBus);
    }

    /**
     * @Route(
     *     "/profile",
     *     name="profile",
     *     methods={"GET"}
     * )
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws Throwable
     */
    public function profile(): Response
    {
        $findQuery = new FindAllQuery($this->loggedUser()->uuid()->toString());
        $tasks = $this->ask($findQuery);
        return $this->render('pages/profile/index.html.twig', [
            'tasks' => $tasks
        ]);
    }

    /**
     * @Route(
     *     "/profile/task/create",
     *     name="profile-create-task",
     *     methods={"GET"}
     * )
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createTasks(): Response
    {
        return $this->render('pages/profile/create-task.html.twig', [
            'form' => $this->taskFormFactory->createForm([
                'userId' => $this->loggedUser()->uuid()
            ])->createView()
        ]);
    }

    /**
     * @Route(
     *     "/profile/task/edit/{uuid}",
     *     name="profile-edit-task",
     *     methods={"GET"}
     * )
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws Throwable
     */
    public function editTasks(string $uuid): Response
    {
        $query = new FindOneByUuidQuery($uuid);
        $task = $this->ask($query);
        // TODO handle not found
        return $this->render('pages/profile/create-task.html.twig', [
            'form' => $this->taskFormFactory->createForm([], TaskFormFactory::REPLACE, $task)->createView()
        ]);
    }

    /**
     * @Route(
     *     "/profile/task/create",
     *     name="post-profile-create-task",
     *     methods={"POST"}
     * )
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws Throwable
     */
    public function postCreateTasks(Request $request): Response
    {
        try {
            $form = $request->request->all('task_symfony_form');

            $createCommand = new CreateTaskCommand(
                $form['uuid'],
                $form['userId'],
                $form['title'],
                isset($form['completedAt'])
            );

            $this->handle($createCommand);

            return $this->redirect('profile', []);
        } catch (FormException|UniqueConstraintViolationException $exception) {

            if ($exception instanceof UniqueConstraintViolationException) {
                $form = $this->taskFormFactory->createForm([
                    'userId' => $this->loggedUser()->uuid()
                ])->createView();

                return $this->render('pages/profile/create-task.html.twig', [
                    'error' => 'Task ID already exists',
                    'form' => $form
                ]);
            }

            return $this->render('pages/profile/create-task.html.twig', [
                'form' => $exception->form->createView()
            ]);
        }
    }

    /**
     * @Route(
     *     "/profile/task/edit/{uuid}",
     *     name="post-profile-edit-task",
     *     methods={"PUT"}
     * )
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws Throwable
     */
    public function postEditTasks(string $uuid, Request $request): Response
    {
        $payload = $request->request->all('task_symfony_form');
        $mutation = new UpdateTaskCommand($payload, $uuid);

        $this->handle($mutation);

        return $this->redirect('profile');
    }
}
