<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AbstractUuid
{
    /**
     * @return static
     */
    public static function fromUuid(UuidInterface $value): self
    {
        return new static($value);
    }

    public function toUuid(): UuidInterface
    {
        return $this->value;
    }

    /**
     * @return static
     */
    public static function fromBytes($bytes): self
    {
        return new static(Uuid::fromBytes($bytes));
    }

    /**
     * @return static
     */
    public static function fromString($name): self
    {
        return new static(Uuid::fromString($name));
    }

    /**
     * @return static
     */
    public static function fromInteger($integer): self
    {
        return new static(Uuid::fromInteger($integer));
    }

    /**
     * @param null|mixed $node
     * @param null|mixed $clockSeq
     *
     * @throws \Exception
     *
     * @return static
     */
    public static function uuid1($node = null, $clockSeq = null): self
    {
        return new static(Uuid::uuid1($node, $clockSeq));
    }

    /**
     * @return static
     */
    public static function uuid3($ns, $name): self
    {
        return new static(Uuid::uuid3($ns, $name));
    }

    /**
     * @throws \Exception
     *
     * @return static
     */
    public static function uuid4(): self
    {
        return new static(Uuid::uuid4());
    }

    /**
     * @return static
     */
    public static function uuid5($ns, $name): self
    {
        return new static(Uuid::uuid5($ns, $name));
    }

    public static function isValid($uuid): bool
    {
        return Uuid::isValid($uuid);
    }

    public function compareTo(self $other)
    {
        $this->value->compareTo($other->toUuid());
    }

    public function equals(self $other)
    {
        $this->value->equals($other->toUuid());
    }

    public function getBytes()
    {
        return $this->value->getBytes();
    }

    public function getInteger()
    {
        return $this->value->getInteger();
    }

    public function toString()
    {
        return $this->value->toString();
    }

    public function __toString()
    {
        return $this->toString();
    }

    private function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    protected $value;
}
