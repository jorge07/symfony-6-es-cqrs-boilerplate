<?php

declare(strict_types=1);

namespace UI\Cli\Command;

use App\Shared\Application\Command\CommandBusInterface;
use App\User\Application\Command\SignUp\SignUpCommand as CreateUser;
use Assert\AssertionFailedException;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CreateUserCommand extends Command
{
    private CommandBusInterface $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        parent::__construct();

        $this->commandBus = $commandBus;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-user')
            ->setDescription('Given a uuid and email, generates a new user.')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('uuid', InputArgument::OPTIONAL, 'User Uuid')
        ;
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $uuid */
        $uuid = $input->getArgument('uuid') ?: Uuid::uuid4()->toString();
        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var string $password */
        $password = $input->getArgument('password');

        $command = new CreateUser($uuid, $email, $password);

        $this->commandBus->handle($command);

        $output->writeln('<info>User Created: </info>');
        $output->writeln('');
        $output->writeln("Uuid: $uuid");
        $output->writeln("Email: $email");

        return 1;
    }
}
