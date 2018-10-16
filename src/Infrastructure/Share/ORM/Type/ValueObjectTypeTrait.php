<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\ORM\Type;

trait ValueObjectTypeTrait
{
    abstract protected function getValueObjectClassName(): string;

    public function getName()
    {
        return $this->getValueObjectClassName();
    }
}
