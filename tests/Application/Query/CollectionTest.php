<?php

declare(strict_types=1);

namespace App\Tests\Application\Query;

use App\Application\Query\Collection;
use App\Infrastructure\Shared\Persistence\ReadModel\Exception\NotFoundException;
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
