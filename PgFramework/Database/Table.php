<?php

declare(strict_types=1);

namespace PgFramework\Database;

use PDO;
use stdClass;

/**
 *
 */
class Table
{
    protected PDO $pdo;

    protected string $table;

    protected ?string $entity = stdClass::class;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function makeQuery(): Query
    {
        return (new Query($this->pdo))
            ->from($this->table, $this->table[0])
            ->into($this->entity);
    }

    public function findList(): array
    {
        $list = [];
        $results = $this->pdo->query("SELECT id, name FROM $this->table")
            ->fetchAll(PDO::FETCH_NUM);
        foreach ($results as $result) {
            $list[$result[0]] = $result[1];
        }
        return $list;
    }

    public function findAll(): Query
    {
        return $this->makeQuery();
    }

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     * @throws NoRecordException
     */
    public function findBy(string $field, string $value): mixed
    {
        return $this->makeQuery()->where("$field = :field")->params(["field" => $value])->fetchOrFail();
    }

    /**
     * @param integer $id
     * @return mixed
     * @throws NoRecordException
     */
    public function find(int $id): mixed
    {
        return $this->makeQuery()->where("id = $id")->fetchOrFail();
    }

    /**
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function update(int $id, array $params): bool
    {
        $fieldsQuery = $this->buildFieldQuery($params);
        $params['id'] = $id;
        $statement = $this->pdo->prepare("UPDATE $this->table SET $fieldsQuery WHERE id=:id");
        return $statement->execute($params);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function insert(array $params): bool
    {
        $fields = array_keys($params);
        $values = join(',', array_map(function ($field) {
            return ':' . $field;
        }, $fields));
        $query = "INSERT INTO $this->table ("
            . join(',', $fields)
            . ") VALUES ("
            . $values
            . ")";
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM $this->table WHERE id=?");
        return $statement->execute([$id]);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM $this->table WHERE id=?");
        $statement->execute([$id]);
        return $statement->fetchColumn() !== false;
    }

    public function count(): mixed
    {
        return $this->makeQuery()->count();
    }

    /**
     * @param array $params
     * @return string
     */
    private function buildFieldQuery(array $params): string
    {
        return join(", ", array_map(function ($field) {
            return "$field=:$field";
        }, array_keys($params)));
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return  PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param string $query
     * @param array $params
     * @return mixed
     * @throws NoRecordException
     */
    protected function fetchOrFail(string $query, array $params = []): mixed
    {
        $query = $this->pdo->prepare($query);
        $query->execute($params);
        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }
        $record = $query->fetch();
        if ($record === false) {
            throw new NoRecordException('');
        }
        return $record;
    }

    /**
     * @param string $query
     * @param array $params
     * @return mixed
     */
    protected function fetchColumn(string $query, array $params = []): mixed
    {
        $query = $this->pdo->prepare($query);
        $query->execute($params);
        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }
        return $query->fetchColumn();
    }
}
