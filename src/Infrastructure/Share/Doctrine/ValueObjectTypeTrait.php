<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Doctrine;

trait ValueObjectTypeTrait
{
    public function getName(): string
    {
        return $this->getValueObjectClassName();
    }

    abstract protected function getValueObjectClassName(): string;
}
