<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Query;

interface QueryHandlerInterface
{
    /**
     * Handle the query and return the requested data
     * @return mixed The requested data
     */
    public function handle(QueryInterface $query): mixed;

}
