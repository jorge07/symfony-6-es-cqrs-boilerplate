<?php

declare(strict_types=1);

namespace App\Shared\Domain\Specification;

abstract class AbstractSpecification
{
    /**
     * @param mixed $value
     */
    abstract public function isSatisfiedBy($value): bool;
}
