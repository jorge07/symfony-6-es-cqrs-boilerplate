<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence\Doctrine\Types;

use App\User\Domain\ValueObject\Auth\HashedPassword;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Throwable;

final class HashedPasswordType extends StringType
{
    private const TYPE = 'hashed_password';

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof HashedPassword) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', HashedPassword::class]);
        }

        return $value->toString();
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?HashedPassword
    {
        if (null === $value || $value instanceof HashedPassword) {
            return $value;
        }

        try {
            $hashedPassword = HashedPassword::fromHash($value);
        } catch (Throwable) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'a valid hashed password');
        }

        return $hashedPassword;
    }

    public function getName(): string
    {
        return self::TYPE;
    }
}
