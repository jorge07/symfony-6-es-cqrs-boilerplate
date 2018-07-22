<?php

declare(strict_types=1);

namespace App\Tests\Application\Query;

use App\Application\Query\Collection;
use App\Domain\Shared\Query\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @test
     *
     * @group unit
     */
    public function must_throw_not_found_exception_on_not_page_found()
    {
        self::expectException(NotFoundException::class);

        new Collection(2, 10, 2, []);
    }
}
