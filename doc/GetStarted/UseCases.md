# Creating a new Use Case

A use case represents an action in the system. These use cases orchestrate the flow of data to and from the entities, and direct those entities to use their enterprise wide business rules to achieve the goals of the use case.
It can be a mutation of the state or a query but not both in a CQRS project.

Let's create a Use Case that just do: `echo "LOOL"`.

### The Command

```php
<?php

declare(strict_types=1);

namespace App\Application\Command\Log;

use App\Infrastructure\Share\Bus\CommandInterface;

class EchoCommand implements CommandInterface
{

}
```

### The handler

```php
<?php

declare(strict_types=1);

namespace App\Application\Command\Log;

use App\Application\Command\CommandHandlerInterface;

class EchoHandler implements CommandHandlerInterface
{
    public function __invoke(EchoCommand $command): void
    {
        echo 'LOOL';
    }
}
```

Now you can use this from UI

### The console command

```php
<?php

declare(strict_types=1);

namespace App\UI\Cli\Command;

use App\Infrastructure\Share\Bus\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EchoCli extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:echo')
            ->setDescription('just an echo')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $echoCommand = new \App\Application\Command\Log\EchoCommand();
        $this->commandBus->handle($echoCommand);
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
```

### Let's test it

Enter in the docker container:

`docker-compose exec php sh -l`

Execute:

`./bin/console app:echo`

And you should see: `LOOL`

And that's all with 0 config thanks to Symfony 4!
