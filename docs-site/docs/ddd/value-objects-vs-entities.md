---
title: Value Objects vs Entities
slug: /ddd/value-objects-vs-entities
---

# Value Objects vs Entities

## Core Concepts

Understanding the difference between Value Objects and Entities is fundamental to Domain-Driven Design. This distinction helps create a more expressive and maintainable domain model.

## Value Objects

**Definition**: Objects that are defined by their attributes rather than their identity. Two value objects with the same attributes are considered equal.

### Characteristics

1. **Immutable**: Cannot be changed after creation
2. **No Identity**: Defined entirely by their attributes
3. **Equality by Value**: Two instances with same values are equal
4. **Side-effect Free**: Operations don't modify the object

### Examples in This Codebase

#### Email Value Object

```php
// src/App/User/Domain/ValueObject/Email.php
final class Email implements JsonSerializable, \Stringable
{
    private function __construct(private readonly string $email)
    {
    }

    public static function fromString(string $email): self
    {
        Assertion::email($email, 'Not a valid email');
        return new self($email);
    }

    public function toString(): string
    {
        return $this->email;
    }

    // Implements equality by value
    public function equals(Email $other): bool
    {
        return $this->email === $other->email;
    }
}
```

**Why Email is a Value Object**:
- No unique identity needed
- Immutable once created
- Behavior focuses on the email value itself
- Two emails with same address are the same

#### DateTime Value Object

```php
// src/App/Shared/Domain/ValueObject/DateTime.php
final class DateTime extends DateTimeImmutable
{
    public const FORMAT = 'Y-m-d\TH:i:s.uP';

    public static function now(): self
    {
        return self::create();
    }

    public static function fromString(string $dateTime): self
    {
        return self::create($dateTime);
    }

    public function toString(): string
    {
        return $this->format(self::FORMAT);
    }
}
```

#### HashedPassword Value Object

```php
// src/App/User/Domain/ValueObject/Auth/HashedPassword.php
final class HashedPassword
{
    private function __construct(private readonly string $hashedPassword)
    {
    }

    public static function fromPlainPassword(string $plainPassword): self
    {
        return new self(password_hash($plainPassword, PASSWORD_ARGON2ID));
    }

    public static function fromHash(string $hashedPassword): self
    {
        return new self($hashedPassword);
    }

    public function match(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hashedPassword);
    }

    public function toString(): string
    {
        return $this->hashedPassword;
    }
}
```

### Value Object Benefits

1. **Expressiveness**: `Email` is more expressive than `string`
2. **Validation**: Ensures data integrity at creation
3. **Behavior**: Encapsulates related operations
4. **Type Safety**: Prevents mixing incompatible values

```php
// Type-safe method signatures
public function changeEmail(Email $email): void
{
    // Can't accidentally pass a string or other type
}
```

## Entities

**Definition**: Objects that have a distinct identity that runs through time and different representations. They are distinguished by their identity, not their attributes.

### Characteristics

1. **Unique Identity**: Has an identifier that doesn't change
2. **Mutable**: Attributes can change over time
3. **Equality by Identity**: Same ID means same entity
4. **Lifecycle**: Can be created, modified, and potentially deleted

### Examples in This Codebase

#### User Entity (Aggregate Root)

```php
// src/App/User/Domain/User.php
class User extends EventSourcedAggregateRoot
{
    private UuidInterface $uuid;           // Identity
    private Email $email;                  // Mutable attribute
    private HashedPassword $hashedPassword; // Mutable attribute
    private ?DateTime $createdAt = null;   // Immutable once set
    private ?DateTime $updatedAt = null;   // Changes over time

    public static function create(
        UuidInterface $uuid,
        Credentials $credentials,
        UniqueEmailSpecificationInterface $uniqueEmailSpecification
    ): self {
        // Entity creation with identity
        $user = new self();
        $user->apply(new UserWasCreated($uuid, $credentials, DateTime::now()));
        return $user;
    }

    public function changeEmail(Email $email): void
    {
        // Entity behavior that changes state
        $this->apply(new UserEmailChanged($this->uuid, $email, DateTime::now()));
    }

    public function getAggregateRootId(): string
    {
        return $this->uuid->toString(); // Identity never changes
    }
}
```

**Why User is an Entity**:
- Has unique identity (UUID)
- Attributes can change over time (email, password)
- Maintains identity through lifecycle
- Same UUID always refers to the same user

### Entity Identity

Identity can be:

1. **Natural Identity**: Business-meaningful identifier
2. **Surrogate Identity**: Technical identifier (UUID, auto-increment)

```php
// UUID as surrogate identity
private UuidInterface $uuid;

// Email could be natural identity in some contexts
// but here it's changeable, so not suitable as identity
private Email $email;
```

## Comparison Table

| Aspect | Value Object | Entity |
|--------|--------------|---------|
| **Identity** | No unique identity | Has unique identity |
| **Mutability** | Immutable | Mutable |
| **Equality** | By value/attributes | By identity |
| **Lifespan** | Created and used | Full lifecycle |
| **Examples** | Email, Money, Address | User, Order, Product |

## Practical Guidelines

### When to Use Value Objects

1. **Descriptive Properties**: Email, phone number, address
2. **Measurements**: Money, quantity, weight
3. **Ranges**: Date range, age range
4. **Complex Attributes**: Coordinates, color

### When to Use Entities

1. **Things with Identity**: User, order, product
2. **Trackable Objects**: Account, booking, subscription
3. **Long-lived Objects**: Customer, employee
4. **Things that Change**: Aggregate roots with mutable state

### Converting Between Types

Sometimes requirements change:

```php
// Initially a value object
class Address
{
    private string $street;
    private string $city;
    // No identity needed
}

// Later becomes an entity if we need to track it
class Address extends Entity
{
    private AddressId $id;        // Now has identity
    private string $street;
    private string $city;
    private DateTime $createdAt;  // Lifecycle tracking
}
```

## Testing Value Objects vs Entities

### Testing Value Objects

```php
class EmailTest extends TestCase
{
    public function testEmailEqualityByValue(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        
        $this->assertTrue($email1->equals($email2));
        $this->assertEquals($email1->toString(), $email2->toString());
    }

    public function testEmailImmutability(): void
    {
        $email = Email::fromString('test@example.com');
        $originalValue = $email->toString();
        
        // No methods to change the email exist
        $this->assertEquals($originalValue, $email->toString());
    }
}
```

### Testing Entities

```php
class UserTest extends TestCase
{
    public function testUserIdentityPersistence(): void
    {
        $uuid = Uuid::uuid4();
        $user = User::create($uuid, $credentials, $specification);
        
        $originalEmail = $user->email();
        $user->changeEmail(Email::fromString('new@example.com'));
        
        // Identity remains the same
        $this->assertEquals($uuid->toString(), $user->uuid());
        // But attributes can change
        $this->assertNotEquals($originalEmail, $user->email());
    }
}
```

## Best Practices

### 1. **Favor Value Objects**
Use value objects when possible - they're simpler and safer

### 2. **Validate in Constructor**
Ensure value objects are always in valid state

```php
public static function fromString(string $email): self
{
    Assertion::email($email, 'Not a valid email');
    return new self($email);
}
```

### 3. **Implement Equality for Value Objects**
Make comparison operations explicit

### 4. **Use Type Hints**
Leverage value objects for better type safety

### 5. **Keep Entities Focused**
Entities should have clear, single responsibilities

This distinction between Value Objects and Entities is crucial for building a rich, expressive domain model that accurately represents business concepts and their relationships.