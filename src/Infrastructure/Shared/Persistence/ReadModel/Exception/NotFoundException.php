<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\ReadModel\Exception;

final class NotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Resource not found');
    }
}
