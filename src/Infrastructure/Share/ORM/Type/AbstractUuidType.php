<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\ORM\Type;

use App\Domain\Shared\ValueObject\AbstractUuid;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Ramsey\Uuid\Doctrine\UuidBinaryType;

abstract class AbstractUuidType extends UuidBinaryType
{
    use ValueObjectTypeTrait;

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToPHPValue($value, $platform);

        if (empty($value)) {
            return null;
        }

        return \call_user_func($this->getValueObjectClassName() . '::fromUuid', $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if (!($value instanceof AbstractUuid)) {
            throw new \Exception(sprintf(
                'Error while convert php value to database value, AbstractUuid expected but %s got',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        $value = $value->toUuid();

        return parent::convertToDatabaseValue($value, $platform);
    }
}
