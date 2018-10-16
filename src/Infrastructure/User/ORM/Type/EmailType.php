<?php

declare(strict_types=1);

namespace App\Infrastructure\User\ORM\Type;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\ORM\Type\AbstractStringType;

class EmailType extends AbstractStringType
{
    protected function getValueObjectClassName(): string
    {
        return Email::class;
    }
}
