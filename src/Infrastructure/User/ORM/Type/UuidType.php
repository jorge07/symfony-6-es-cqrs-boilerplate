<?php

declare(strict_types=1);

namespace App\Infrastructure\User\ORM\Type;

use App\Domain\User\ValueObject\Uuid;
use App\Infrastructure\Share\ORM\Type\AbstractUuidType;

class UuidType extends AbstractUuidType
{
    public function getValueObjectClassName(): string
    {
        return Uuid::class;
    }
}
