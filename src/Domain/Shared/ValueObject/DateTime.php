<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use App\Domain\Shared\Exception\DateTimeException;

class DateTime
{
    const FORMAT = \DateTimeImmutable::RFC3339_EXTENDED;

    /**
     * @throws DateTimeException
     */
    public static function now(): self
    {
        return self::create();
    }

    /**
     * @throws DateTimeException
     */
    public static function fromString(string $dateTime): self
    {
        return self::create($dateTime);
    }

    /**
     * @throws DateTimeException
     */
    private static function create(string $dateTime = ''): self
    {
        $self = new self();

        try {
            $self->dateTime = new \DateTimeImmutable($dateTime);
        } catch (\Exception $e) {
            throw new DateTimeException($e);
        }

        return $self;
    }

    public function toString(): string
    {
        return $this->dateTime->format(self::FORMAT);
    }

    public function toFormat(string $format): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * @var \DateTimeImmutable
     */
    private $dateTime;
}
