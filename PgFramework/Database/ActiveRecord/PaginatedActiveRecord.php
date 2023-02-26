<?php

declare(strict_types=1);

namespace PgFramework\Database\ActiveRecord;

use Pagerfanta\Adapter\AdapterInterface;

class PaginatedActiveRecord implements AdapterInterface
{
    protected string|PaginatedModel $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * @return int
     */
    public function getNbResults(): int
    {
        return $this->model::getNbResults();
    }

    /**
     * @param int $offset
     * @param int $length
     * @return PaginatedModel[]
     */
    public function getSlice($offset, $length): array
    {
        return $this->model::paginatedQuery($offset, $length);
    }
}
