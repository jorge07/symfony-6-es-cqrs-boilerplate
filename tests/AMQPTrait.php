<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Transport\AmqpExt\Connection;

trait AMQPTrait
{
    private $application;

    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    public function purgeQueue(): void
    {
        $connection = Connection::fromDsn(getenv('MESSENGER_TRANSPORT_DSN'));
        $connection->purgeQueues();
    }

    public function consumeMessages(): void
    {
        $command = $this->application->find('messenger:consume');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'receivers' => ['events'],
            '--time-limit' => 10,
        ]);
    }
}