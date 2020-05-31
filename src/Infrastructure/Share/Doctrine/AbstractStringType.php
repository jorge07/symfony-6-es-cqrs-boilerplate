<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Doctrine;

use App\Domain\Shared\ValueObject\AbstractString;
use Assert\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

abstract class AbstractStringType extends StringType
{
    use ValueObjectTypeTrait;

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToPHPValue($value, $platform);

        if (empty($value)) {
            return null;
        }

        try {
            return \call_user_func($this->getValueObjectClassName() . '::fromString', $value);
        } catch (InvalidArgumentException $exception) {
            throw ConversionException::conversionFailed(
                $value,
                $this->getName(),
                $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof AbstractString) {
            $value = $value->toString();

            return parent::convertToDatabaseValue($value, $platform);
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', AbstractString::class]
        );
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
