<?php

declare(strict_types=1);

namespace App\Application\Query;

class Collection
{
    /**
     * @var int
     */
    public $page;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $total;

    /**
     * @var Item[]
     */
    public $data;

    public function __construct(int $page, int $limit, int $total, array $data)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->total = $total;
        $this->data = $data;
    }
}
