<?php

declare(strict_types=1);

namespace App\UI\Cli\Command;

use League\Tactician\CommandBus;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Application\Command\User\Create\CreateUserCommand as CreateUser;

class CreateUserCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('app:create-user')
            ->setDescription('Given a uuid and email, generates a new user.')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('uuid', InputArgument::OPTIONAL, 'User Uuid')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new CreateUser(
            $uuid = ($input->getArgument('uuid') ?: Uuid::uuid4()->toString()),
            $email = $input->getArgument('email')
        );

        $this->commandBus->handle($command);

        $output->writeln('<info>User Created: </info>');
        $output->writeln('');
        $output->writeln("Uuid: $uuid");
        $output->writeln("Email: $email");
    }

    public function __construct(CommandBus $commandBus)
    {
        parent::__construct();
        $this->commandBus = $commandBus;
    }

    /**
     * @var CommandBus
     */
    private $commandBus;
}
