<?php

declare(strict_types=1);

namespace App\Application\Query;

interface QueryBusInterface
{
    /**
     * @param QueryInterface $query
     * @return mixed
     */
    public function ask(QueryInterface $query);

}
