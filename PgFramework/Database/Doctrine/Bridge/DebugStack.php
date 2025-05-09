<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine\Bridge;

use function microtime;

class DebugStack implements DebugStackInterface
{
    /**
     * Executed SQL queries.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $queries = [];

    /**
     * If Debug Stack is enabled (log queries) or not.
     *
     * @var bool
     */
    public bool $enabled = true;

    /** @var float|null */
    public ?float $start = null;

    /** @var int */
    public int $currentQuery = 0;

    public function startQuery(string $connectionName, $sql, ?array $params = null, ?array $types = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->start = microtime(true);

        $this->queries[++$this->currentQuery] = [
            'connection' => $connectionName,
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'executionMS' => 0,
        ];
    }

    public function stopQuery(): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->queries[$this->currentQuery]['executionMS'] = microtime(true) - $this->start;
    }
}
