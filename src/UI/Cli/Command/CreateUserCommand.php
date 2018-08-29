<?php

declare(strict_types=1);

namespace App\UI\Cli\Command;

use App\Application\Command\User\SignUp\SignUpCommand as CreateUser;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateUserCommand extends Command
{
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
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new CreateUser(
            $uuid = ($input->getArgument('uuid') ?: Uuid::uuid4()->toString()),
            $email = $input->getArgument('email'),
            $password = $input->getArgument('password')
        );

        $this->commandBus->dispatch($command);

        $output->writeln('<info>User Created: </info>');
        $output->writeln('');
        $output->writeln("Uuid: $uuid");
        $output->writeln("Email: $email");
    }

    public function __construct(MessageBusInterface $commandBus)
    {
        parent::__construct();
        $this->commandBus = $commandBus;
    }

    /**
     * @var MessageBusInterface
     */
    private $commandBus;
}
