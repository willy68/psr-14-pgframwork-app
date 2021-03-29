<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
use Framework\Database\Table;
use Framework\Database\Query;

/**
 *
 */
class PostTable extends Table
{

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $entity = Post::class;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * Undocumented function
     *
     * @return Query
     */
    public function findAll(): Query
    {
        $category = new CategoryTable($this->pdo);
        return $this->makeQuery()
            ->select('p.*, c.name AS category_name, c.slug AS category_slug')
            ->join($category->getTable() . ' AS c', 'c.id = p.category_id')
            ->order('p.created_at DESC');
    }

    /**
     * Undocumented function
     *
     * @return Query
     */
    public function findPublic(): Query
    {
        return $this->findAll()
            ->where('p.published = 1')
            ->where('p.created_at < NOW()');
    }

    /**
     * Undocumented function
     *
     * @param int $category_id
     * @return Query
     */
    public function findPublicForCategory(int $category_id): Query
    {
        return $this->findPublic()->where("p.category_id = $category_id");
    }

    /**
     * Undocumented function
     *
     * @param int $id
     * @return Post
     */
    public function findWithCategory(int $id): Post
    {
        return $this->findPublic()->where("p.id = $id")->fetch();
    }
}
