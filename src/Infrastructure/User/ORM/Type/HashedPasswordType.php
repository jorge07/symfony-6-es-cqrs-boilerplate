<?php

declare(strict_types=1);

namespace App\Infrastructure\User\ORM\Type;

use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Infrastructure\Share\ORM\Type\ValueObjectTypeTrait;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class HashedPasswordType extends StringType
{
    use ValueObjectTypeTrait;

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToPHPValue($value, $platform);

        if (empty($value)) {
            return null;
        }

        return HashedPassword::fromHash($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        /** @var HashedPassword $value */
        $value = $value->toString();

        return parent::convertToDatabaseValue($value, $platform);
    }

    protected function getValueObjectClassName(): string
    {
        return HashedPassword::class;
    }
}
