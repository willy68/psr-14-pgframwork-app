<?php

declare(strict_types=1);

namespace PgFramework\Database\ActiveRecord;

use ActiveRecord;
use ActiveRecord\Exceptions\RecordNotFound;
use ActiveRecord\Model;
use Pagerfanta\Pagerfanta;
use PgFramework\Database\Query;

class PaginatedModel extends ActiveRecord\Model
{
    public static array $paginatedCondition = [];

    public static ?Query $query = null;

    /**
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     */
    public static function paginate(int $perPage, int $currentPage = 1): Pagerfanta
    {
        $paginator = new PaginatedActiveRecord(static::class);
        return (new Pagerfanta($paginator))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    /**
     * @return int
     */
    public static function getNbResults(): int
    {
        $options = null;
        if (!empty(static::$paginatedCondition['conditions'])) {
            $options['conditions'] = static::$paginatedCondition['conditions'];
        }
        return static::count($options);
    }

    /**
     * @param int $offset
     * @param int $length
     * @return Model|array|mixed|null
     * @throws RecordNotFound
     */
    public static function paginatedQuery(int $offset, int $length): mixed
    {
        static::$paginatedCondition['limit'] = $length;
        static::$paginatedCondition['offset'] = $offset;
        return static::find('all', static::$paginatedCondition);
    }

    /**
     * set paginated options conditions
     *
     * @param Query $query
     * @return string static::class
     */
    public static function setPaginatedQuery(Query $query): string
    {
        if (!empty($where = $query->getWhere())) {
            static::$paginatedCondition['conditions'] = [$where];
        }
        if (!empty($order = $query->getOrder())) {
            static::$paginatedCondition['order'] = $order;
        }
        return static::class;
    }

    /**
     * Init options conditions for all Post
     *
     * @return Query
     */
    public static function findAll(): Query
    {
        return static::makeQuery();
    }

    /**
     * @param array $field
     * @return array
     * @throws RecordNotFound
     */
    public static function findList(array $field): array
    {
        return static::find('all', ['select' => join(", ", $field)]);
    }

    /**
     * Init query
     *
     * @return Query
     */
    public static function makeQuery(): Query
    {
        if (!static::$query) {
            return static::$query = new Query();
        }
        return static::$query;
    }
}
