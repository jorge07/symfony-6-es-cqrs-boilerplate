<?php

declare(strict_types=1);

namespace App\Infrastructure\Share\Query\Projections;

interface ViewItem
{
    public function id(): string;
}
