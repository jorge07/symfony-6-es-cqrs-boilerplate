# Command Bus and Query Bus

### Why tactician bus and not broadway bus?

Broadway has a CommandBus implementation but not a QueryBus. The interface does not allow you to return content so you 
need to build your own.

Tactician `CommandHandlerMiddleware::execute` has:

```php
  return $handler->{$methodName}($command);
```

It allows you to return content from you `Handlers`, something required for a QueryBus. 

The configuration for a Symfony app will be like that:

```yaml
tactician:
    default_bus: command
    method_inflector: tactician.handler.method_name_inflector.invoke
    commandbus:
        query:
            middleware:
                - tactician.commandbus.query.middleware.command_handler
        command:
            middleware:
                - tactician.commandbus.command.middleware.command_handler
```

So you can create your own middleware for example to generate a backend caching for your read model. 

```yaml
tactician:
	...
    commandbus:
        query:
            middleware:
+               - app.bus.query.middleware.cache
                - tactician.commandbus.query.middleware.command_handler
```