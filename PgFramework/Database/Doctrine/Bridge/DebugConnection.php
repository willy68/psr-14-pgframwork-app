<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine\Bridge;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;

class DebugConnection extends AbstractConnectionMiddleware
{
    private int $level = 0;
    private DebugStack $debugStack;
    private string $connectionName;

    /** @internal This connection can be only instantiated by its driver. */
    public function __construct(
        ConnectionInterface $connection,
        DebugStackInterface $debugStack,
        string $connectionName
    ) {
        parent::__construct($connection);

        $this->debugStack = $debugStack;
        $this->connectionName = $connectionName;
    }
    /*
        public function __destruct()
        {
            $this->logger->info('Disconnecting');
        }
    */
    /**
     * {@inheritDoc}
     */
    public function prepare(string $sql): DriverStatement
    {
        return new DebugStatement(
            parent::prepare($sql),
            $this->debugStack,
            $sql,
            $this->connectionName
        );
    }

    /**
     * {@inheritDoc}
     */
    public function query(string $sql): Result
    {
        $this->debugStack->startQuery($this->connectionName, $sql);

        $result = parent::query($sql);

        $this->debugStack->stopQuery();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function exec(string $sql): int
    {
        $this->debugStack->startQuery($this->connectionName, $sql);

        $result = parent::exec($sql);

        $this->debugStack->stopQuery();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction(): bool
    {
        if (1 === ++$this->level) {
            $this->debugStack->startQuery($this->connectionName, 'START TRANSACTION');
        }

        try {
            $ret = parent::beginTransaction();
        } finally {
            $this->debugStack->stopQuery();
        }
        return $ret;
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): bool
    {
        if (1 === $this->level--) {
            $this->debugStack->startQuery($this->connectionName, 'COMMIT');
        }

        try {
            $ret = parent::commit();
        } finally {
            $this->debugStack->stopQuery();
        }
        return $ret;
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack(): bool
    {
        if (1 === $this->level--) {
            $this->debugStack->startQuery($this->connectionName, 'ROLLBACK');
        }

        try {
            $ret = parent::rollBack();
        } finally {
            $this->debugStack->stopQuery();
        }
        return $ret;
    }
}
