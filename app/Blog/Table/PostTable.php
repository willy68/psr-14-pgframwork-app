<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
use PgFramework\Database\Query;
use PgFramework\Database\Table;

class PostTable extends Table
{
    protected ?string $entity = Post::class;

    protected string $table = 'posts';

    public function findAll(): Query
    {
        $category = new CategoryTable($this->pdo);
        return $this->makeQuery()
            ->select('p.*, c.name AS category_name, c.slug AS category_slug')
            ->join($category->getTable() . ' AS c', 'c.id = p.category_id')
            ->order('p.created_at DESC');
    }

    public function findPublic(): Query
    {
        return $this->findAll()
            ->where('p.published = 1')
            ->where('p.created_at < NOW()');
    }

    /**
     * @param int $category_id
     * @return Query
     */
    public function findPublicForCategory(int $category_id): Query
    {
        return $this->findPublic()->where("p.category_id = $category_id");
    }

    /**
     * @param int $id
     * @return Post
     */
    public function findWithCategory(int $id): Post
    {
        return $this->findPublic()->where("p.id = $id")->fetch();
    }
}
