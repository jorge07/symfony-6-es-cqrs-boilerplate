<?php

namespace App\UI\Cli\Command;

use App\Application\Command\Event\ReplayEventsCommand;
use App\Domain\Shared\Event\Repository\IterableAggregateEventStoreInterface;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventStoreReplayCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('event-store:replay')
            ->setDescription('It will replay events in the event store.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new ReplayEventsCommand();

        $this->commandBus->handle($command);

        // ToDo: add failure handling
        $output->writeln('<info>Events replayed.</info>');
    }

    public function __construct(
        CommandBus $commandBus,
        IterableAggregateEventStoreInterface $iterableDbalEventStore
    ) {
        parent::__construct();
        $this->commandBus = $commandBus;
        $this->iterableDbalEventStore = $iterableDbalEventStore;
    }

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var IterableAggregateEventStoreInterface
     */
    private $iterableDbalEventStore;
}
