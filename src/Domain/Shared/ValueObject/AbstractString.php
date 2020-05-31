<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

abstract class AbstractString
{
    protected string $value;

    protected function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return static::create($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    abstract protected static function create(string $value): self;
}
