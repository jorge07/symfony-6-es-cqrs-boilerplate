# CQRS and Event Sourcing

This section explains Command Query Responsibility Segregation (CQRS) and Event Sourcing patterns implemented in this boilerplate.

## What You'll Learn

- **CQRS Overview** - Command/Query separation with benefits
- **Command Bus** - Write operations and business logic execution
- **Event Sourcing** - Storing state as sequence of events
- **Application Workflow** - Complete request flow through the system
- **Symfony Messenger Integration** - Message bus implementation

## Quick Overview

**CQRS (Command Query Responsibility Segregation)** separates read and write operations into different models, allowing each to be optimized for its specific purpose.

**Event Sourcing** stores all changes to application state as a sequence of events, enabling powerful audit trails, temporal queries, and event replay capabilities.

This boilerplate demonstrates:

- **Command/Query Separation**: Different models for reads and writes
- **Event Sourcing**: Aggregates persisted as event streams
- **Event-Driven Architecture**: Loose coupling through domain events
- **Symfony Messenger**: Message bus implementation for commands, queries, and events
- **Projections**: Read models built from event streams
- **Async Processing**: Background event processing with RabbitMQ

All patterns work together to create a scalable, maintainable, and audit-friendly application architecture.
