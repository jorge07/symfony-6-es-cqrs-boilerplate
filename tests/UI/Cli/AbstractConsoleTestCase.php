<?php

declare(strict_types=1);

namespace Tests\UI\Cli;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\App\Shared\Application\ApplicationTestCase;

class AbstractConsoleTestCase extends ApplicationTestCase
{
    final protected function app(Command $command, string $alias): CommandTester
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->add($command);

        $command = $application->find($alias);

        return new CommandTester($command);
    }
}
