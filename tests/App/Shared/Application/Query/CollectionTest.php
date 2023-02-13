<?php

declare(strict_types=1);

namespace Tests\App\Shared\Application\Query;

use App\Shared\Application\Query\Collection;
use App\Shared\Infrastructure\Persistence\ReadModel\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     *
     * @throws NotFoundException
     */
    public function must_throw_not_found_exception_on_not_page_found(): void
    {
        $this->expectException(NotFoundException::class);

        new Collection(2, 10, 2, []);
    }
}
