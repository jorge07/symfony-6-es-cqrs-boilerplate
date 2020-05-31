<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Doctrine;

use App\Domain\User\ValueObject\Email;
use App\Infrastructure\Share\Doctrine\AbstractStringType;

class EmailType extends AbstractStringType
{
    protected function getValueObjectClassName(): string
    {
        return Email::class;
    }
}
