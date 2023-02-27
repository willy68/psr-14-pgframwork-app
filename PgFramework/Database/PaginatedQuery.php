<?php

declare(strict_types=1);

namespace PgFramework\Database;

use Pagerfanta\Adapter\AdapterInterface;

class PaginatedQuery implements AdapterInterface
{
    private Query $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getNbResults(): int
    {
        return $this->query->count();
    }

    /**
     * @param int $offset
     * @param int $length
     * @return QueryResult
     */
    public function getSlice($offset, $length): QueryResult
    {
        $query = clone $this->query;
        return $query->limit($length, $offset)->fetchAll();
    }
}
