<?php

declare(strict_types=1);

namespace PgFramework\Database\ActiveRecord;

use Pagerfanta\Adapter\AdapterInterface;

class PaginatedActiveRecord implements AdapterInterface
{
    protected $model;

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
     * @return \ActiveRecord\Model[]
     */
    public function getSlice($offset, $length)
    {
        return $this->model::paginatedQuery($offset, $length);
    }
}
