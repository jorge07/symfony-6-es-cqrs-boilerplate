<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\ORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

abstract class AbstractStringType extends StringType
{
    use ValueObjectTypeTrait;

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToPHPValue($value, $platform);

        if (empty($value)) {
            return null;
        }

        return \call_user_func($this->getValueObjectClassName() . '::fromString', $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        $value = $value->toString();

        return parent::convertToDatabaseValue($value, $platform);
    }
}
