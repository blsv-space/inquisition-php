<?php

namespace Inquisition\Core\Application\Query;

interface QueryHandlerInterface
{
    /**
     * Handle the query and return the requested data
     * @param QueryInterface $query
     * @return mixed The requested data
     */
    public function handle(QueryInterface $query): mixed;

}