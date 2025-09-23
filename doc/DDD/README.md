# Domain-Driven Design (DDD) Concepts

This section explains the core Domain-Driven Design concepts implemented in this boilerplate and the reasoning behind architectural decisions.

## Table of Contents

1. [DDD Objectives and Reasoning](./Objectives.md)
2. [Bounded Context](./BoundedContext.md)
3. [Anti-Corruption Layer](./AntiCorruptionLayer.md)
4. [Domain Modeling](./DomainModeling.md)
5. [Value Objects vs Entities](./ValueObjectsVsEntities.md)
6. [Aggregates and Aggregate Root](./Aggregates.md)
7. [Layered Architecture](./LayeredArchitecture.md)
8. [Repository Pattern](./Repository.md)
9. [Domain Services](./DomainServices.md)
10. [Domain Events](./DomainEvents.md)

## Quick Overview

Domain-Driven Design is a software development methodology that focuses on creating a shared understanding of the business domain through ubiquitous language and strategic design patterns.

This boilerplate demonstrates:

- **Tactical DDD Patterns**: Value Objects, Entities, Aggregates, Domain Services
- **Strategic DDD Patterns**: Bounded Contexts, Context Mapping
- **CQRS Implementation**: Separate read and write models
- **Event Sourcing**: Event-driven domain modeling
- **Clean Architecture**: Hexagonal architecture with dependency inversion

Each concept is implemented with practical examples from the User domain in this codebase.