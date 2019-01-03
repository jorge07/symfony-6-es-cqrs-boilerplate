<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

abstract class AbstractString
{
    /**
     * @return static
     */
    public static function fromString(string $value): self
    {
        return new static($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function __construct(string $value)
    {
        $this->value = $value;
    }

    /** @var string */
    protected $value;
}
