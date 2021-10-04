<?php

declare(strict_types=1);

namespace App\Task\Domain;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

final class Task
{
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $completedAt;

    public function __construct(
        private UuidInterface $uuid,
        private UuidInterface $userId,
        private string $title,
        bool $completed = false,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->completedAt = $completed ? new DateTimeImmutable() : null;
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }

    public function getUUId(): UuidInterface
    {
        return $this->uuid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): bool
    {
        return (bool) $this->completedAt;
    }

    public function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(bool $completedAt): void
    {
        $this->completedAt = $completedAt ? new DateTimeImmutable() : null;
    }
}
