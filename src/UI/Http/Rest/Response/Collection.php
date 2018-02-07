<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Response;

class Collection
{
    public function page(): int
    {
        return $this->page;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function __construct(int $page, int $limit, int $total, array $data)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->total = $total;
        $this->data = $data;
    }
    /**
     * @var int
     */
    private $page;
    /**
     * @var int
     */
    private $limit;
    /**
     * @var int
     */
    private $total;
    /**
     * @var array
     */
    private $data;
}
