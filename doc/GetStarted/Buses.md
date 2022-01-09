# Command Bus, Query Bus and Async Event Bus

### Symfony Messenger Component

[Symfony Messenger](https://symfony.com/doc/current/messenger.html) is what we use to distribute messages synchronous and asynchronously.

We've 3 different type of bus:

- Command: `public function handle(CommandInterface $command): void`
- Query: `public function handle(QueryInterface $query): Item|Collection|string|int|null`
- Async Event: `public function handle(EventInterface $event): void`
	
To define a new use case just implement the required interfaces.

Use `./bin/console debug:messenger` to check the configuration.
