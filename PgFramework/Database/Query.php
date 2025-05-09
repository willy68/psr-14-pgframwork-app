<?php

declare(strict_types=1);

namespace PgFramework\Database;

use IteratorAggregate;
use Pagerfanta\Pagerfanta;
use PDO;
use PDOStatement;
use Traversable;

class Query implements IteratorAggregate
{
    private ?PDO $pdo;

    private array $select = [];

    private array $from = [];

    private mixed $entity;

    private array $where = [];

    private mixed $group;

    private array $order = [];

    private string $limit;

    private array $joins = [];

    private array $params = [];

    /**
     * Query constructor.
     * @param PDO|null $pdo
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $table
     * @param string|null $alias
     * @return self
     */
    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }
        // $this->from[] = $table;
        return $this;
    }

    /**
     * @param string $entity
     * @return self
     */
    public function into(string $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @param string ...$fields
     * @return self
     */
    public function select(string ...$fields): self
    {
        $this->select = $fields;
        return $this;
    }

    /**
     * @param int $length
     * @param int $offset
     * @return self
     */
    public function limit(int $length, int $offset = 0): self
    {
        $this->limit = "$offset, $length";
        return $this;
    }

    /**
     * @param string $orders
     * @return self
     */
    public function order(string $orders): self
    {
        $this->order[] = $orders;
        return $this;
    }

    /**
     * @param string $table
     * @param string $condition
     * @param string $type
     * @return self
     */
    public function join(string $table, string $condition, string $type = 'LEFT'): self
    {
        $this->joins[$type][] = [$table, $condition, $type];
        return $this;
    }

    /**
     * @param string ...$condition
     * @return self
     */
    public function where(string ...$condition): self
    {
        $this->where = array_merge($this->where, $condition);
        return $this;
    }

    /**
     * @return mixed
     */
    public function count(): mixed
    {
        $query = clone $this;
        $table = current($this->from);
        return $query->select("COUNT($table.id)")->execute()->fetchColumn();
    }

    /**
     * @param array $params
     * @return self
     */
    public function params(array $params): self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * @return QueryResult
     */
    public function fetchAll(): QueryResult
    {
        return new QueryResult(
            $this->execute()->fetchAll(PDO::FETCH_ASSOC),
            $this->entity
        );
    }

    /**
     * @return mixed
     */
    public function fetch(): mixed
    {
        $record = $this->execute()->fetch(PDO::FETCH_ASSOC);
        if ($record === false) {
            return false;
        }
        if ($this->entity) {
            return Hydrator::hydrate($record, $this->entity);
        }
        return $record;
    }

    /**
     * @return mixed
     * @throws NoRecordException
     */
    public function fetchOrFail(): mixed
    {
        $record = $this->fetch();
        if ($record === false) {
            throw new NoRecordException();
        }
        return $record;
    }

    /**
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     */
    public function paginate(int $perPage, int $currentPage = 1): Pagerfanta
    {
        $paginator = new PaginatedQuery($this);
        return (new Pagerfanta($paginator))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $parts = ['SELECT'];
        if ($this->select) {
            $parts[] = join(', ', $this->select);
        } else {
            $parts[] = '*';
        }
        $parts[] = 'FROM';
        $parts[] = $this->buildFrom();
        if (!empty($this->joins)) {
            foreach ($this->joins as $type => $joins) {
                foreach ($joins as [$table, $condition]) {
                    $parts [] = strtoupper($type) . " JOIN $table ON $condition";
                }
            }
        }
        if (!empty($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = "(" . join(') AND (', $this->where) . ")";
        }
        if (!empty($this->order)) {
            $parts [] = 'ORDER BY';
                $parts[] = join(', ', $this->order);
        }
        if ($this->limit) {
            $parts[] = 'LIMIT ' . $this->limit;
        }

        return join(' ', $parts);
    }

    /**
     * Get Where conditions
     *
     * @return string
     */
    public function getWhere(): string
    {
        if (!empty($this->where)) {
            return "(" . join(') AND (', $this->where) . ")";
        }
        return '';
    }

    /**
     * Get Order conditions
     *
     * @return string
     */
    public function getOrder(): string
    {
        if (!empty($this->order)) {
            return join(', ', $this->order);
        }
        return '';
    }

    /**
     * Get Limit conditions
     *
     * @return string
     */
    public function getLimit(): string
    {
        if ($this->limit) {
            return $this->limit;
        }
        return '';
    }

    /**
     * Build FROM string
     *
     * @return string
     */
    private function buildFrom(): string
    {
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$key as $value";
            } else {
                $from[] = $value;
            }
        }
        return join(', ', $from);
    }

    /**
     * @return PDOStatement|bool
     */
    private function execute(): PDOStatement|bool
    {
        $query = $this->__toString();
        if (!empty($this->params)) {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($this->params);
            return $stmt;
        }
        return $this->pdo->query($query);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return $this->fetchAll();
    }
}
