# CQRS and Event Sourcing

This section explains Command Query Responsibility Segregation (CQRS) and Event Sourcing patterns implemented in this boilerplate.

## Table of Contents

1. [CQRS Overview](./CQRSOverview.md)
2. [Command Bus](./CommandBus.md)
3. [Query Bus](./QueryBus.md)  
4. [Event Sourcing](./EventSourcing.md)
5. [Event-Driven Design](./EventDrivenDesign.md)
6. [Read and Write Models](./ReadWriteModels.md)
7. [Application Workflow](./ApplicationWorkflow.md)
8. [Symfony Messenger Integration](./SymfonyMessenger.md)

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