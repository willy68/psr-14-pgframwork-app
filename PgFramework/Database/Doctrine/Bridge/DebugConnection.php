<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine\Bridge;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;

class DebugConnection extends AbstractConnectionMiddleware
{
    private DebugStack $debugStack;

    /** @internal This connection can be only instantiated by its driver. */
    public function __construct(ConnectionInterface $connection, DebugStackInterface $debugStack)
    {
        parent::__construct($connection);

        $this->debugStack = $debugStack;
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
        );
    }

    /**
     * {@inheritDoc}
     */
    public function query(string $sql): Result
    {
        $this->debugStack->startQuery($sql);

        $result = parent::query($sql);

        $this->debugStack->stopQuery();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function exec(string $sql): int
    {
        $this->debugStack->startQuery($sql);

        $result = parent::exec($sql);

        $this->debugStack->stopQuery();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction(): bool
    {
        return parent::beginTransaction();
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): bool
    {
        return parent::commit();
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack(): bool
    {
        return parent::rollBack();
    }
}
