<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence\Doctrine\Types;

use App\User\Domain\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Throwable;

final class EmailType extends StringType
{
    private const TYPE = 'email';

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Email) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', Email::class]);
        }

        return $value->toString();
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Email
    {
        if (null === $value || $value instanceof Email) {
            return $value;
        }

        try {
            $email = Email::fromString($value);
        } catch (Throwable) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'a valid email address');
        }

        return $email;
    }

    public function getName(): string
    {
        return self::TYPE;
    }
}
