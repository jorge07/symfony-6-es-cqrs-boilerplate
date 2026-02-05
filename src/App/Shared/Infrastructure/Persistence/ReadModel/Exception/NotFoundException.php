<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\ReadModel\Exception;

use App\Shared\Domain\Exception\NotFoundException as DomainNotFoundException;

/**
 * @deprecated Use App\Shared\Domain\Exception\NotFoundException instead.
 */
final class NotFoundException extends DomainNotFoundException
{
}
