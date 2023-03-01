<?php

namespace PgFramework\Database\Doctrine\Bridge;

interface DebugStackInterface
{
    public function startQuery(string $connectionName, $sql, ?array $params = null, ?array $types = null): void;

    public function stopQuery(): void;
}
