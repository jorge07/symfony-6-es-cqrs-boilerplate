<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Doctrine;

use App\Domain\User\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Throwable;

final class EmailType extends StringType
{
    private const TYPE = 'email';

    /**
     * @param ?Email $value
     * @param AbstractPlatform $platform
     * @return mixed|string|null
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof Email) {
            return $value->toString();
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', Email::class]);
    }

    /**
     * @param string $value
     * @param AbstractPlatform $platform
     * @return Email|null
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof Email) {
            return $value;
        }

        try {
            $email = Email::fromString($value);
        } catch (Throwable $e) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::TYPE;
    }
}
