<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Shared\Query\Exception\NotFoundException;

class Collection
{
    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /** @var int */
    public $total;

    /** @var Item[] */
    public $data;

    /**
     * @throws NotFoundException
     */
    public function __construct(int $page, int $limit, int $total, array $data)
    {
        $this->exists($page, $limit, $total);
        $this->page = $page;
        $this->limit = $limit;
        $this->total = $total;
        $this->data = $data;
    }

    /**
     * @throws NotFoundException
     */
    private function exists(int $page, int $limit, int $total): void
    {
        if (($limit * ($page - 1)) >= $total) {
            throw new NotFoundException();
        }
    }
}
