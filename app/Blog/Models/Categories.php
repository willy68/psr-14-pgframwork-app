<?php

namespace App\Blog\Models;

use PgFramework\Database\ActiveRecord\PaginatedModel;
use PgFramework\Database\Query;

class Categories extends PaginatedModel
{
    public static $connection = 'blog';

    public static $table_name = 'categories';

    public static $has_many = [['posts', 'class_name' => 'Posts']];

    /**
     * Init options conditions for all Post
     *
     * @return Query
     */
    public static function findAll(): Query
    {
        return static::makeQuery()->order('id DESC');
    }

    /**
     *
     *
     * @return array
     */
    public static function findList(array $field): array
    {
        $list = [];
        $results = parent::findList($field);
        foreach ($results as $result) {
            $list[$result->id] = $result->name;
        }
        return $list;
    }
}
