<?php

declare(strict_types=1);

namespace App\Domain\Shared\Specification;

abstract class AbstractSpecification
{
    abstract public function isSatisfiedBy($value): bool;

    final public function not($value): bool
    {
        return !$this->isSatisfiedBy($value);
    }
}
