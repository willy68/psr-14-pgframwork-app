<?php

declare(strict_types=1);

namespace PgFramework\Database;

class QueryResult implements \ArrayAccess, \Iterator
{
    /**
     * @var array
     */
    private $records;

    /**
     * @var mixed
     */
    private $entity;

    /**
     * @var int
     */
    private $index = 0;

    private $hydratedRecords = [];

    /**
     * @param array $records
     * @param string|null $entity
     */
    public function __construct(array $records, ?string $entity = null)
    {
        $this->records = $records;
        $this->entity = $entity;
    }

    /**
     * @param int $index
     * @return mixed
     */
    public function get(int $index)
    {
        if ($this->entity) {
            if (!isset($this->hydratedRecords[$this->index])) {
                $this->hydratedRecords[$this->index] = Hydrator::hydrate($this->records[$index], $this->entity);
            }
            return $this->hydratedRecords[$this->index];
        }
        return $this->entity;
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->records[$offset]);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new \Exception('Can\'t alter records');
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new \Exception('Can\'t alter records');
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->get($this->index);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function next(): void
    {
        $this->index ++;
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->records[$this->index]);
    }
}
