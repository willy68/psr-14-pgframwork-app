<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine\Bridge;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\Deprecations\Deprecation;

use function array_slice;
use function func_get_args;
use function func_num_args;

class DebugStatement extends AbstractStatementMiddleware
{
    private DebugStackInterface $debugStack;
    private string $sql;

    /** @var array<int,mixed>|array<string,mixed> */
    private array $params = [];

    /** @var array<int,int>|array<string,int> */
    private array $types = [];

    /** @internal This statement can be only instantiated by its connection. */
    public function __construct(StatementInterface $statement, DebugStackInterface $debugStack, string $sql)
    {
        parent::__construct($statement);

        $this->debugStack = $debugStack;
        $this->sql    = $sql;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Use {@see bindValue()} instead.
     */
    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): bool
    {
        Deprecation::trigger(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/5563',
            '%s is deprecated. Use bindValue() instead.',
            __METHOD__,
        );

        if (func_num_args() < 3) {
            Deprecation::trigger(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/5558',
                'Not passing $type to Statement::bindParam() is deprecated.'
                . ' Pass the type corresponding to the parameter being bound.',
            );
        }

        $this->params[$param] = &$variable;
        $this->types[$param]  = $type;

        return parent::bindParam($param, $variable, $type, ...array_slice(func_get_args(), 3));
    }

    /**
     * {@inheritdoc}
     */
    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        if (func_num_args() < 3) {
            Deprecation::trigger(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/5558',
                'Not passing $type to Statement::bindValue() is deprecated.'
                . ' Pass the type corresponding to the parameter being bound.',
            );
        }

        $this->params[$param] = $value;
        $this->types[$param]  = $type;

        return parent::bindValue($param, $value, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($params = null): ResultInterface
    {
        $this->debugStack->startQuery($this->sql, $params ?? $this->params, $this->types);

        $result = parent::execute($params);

        $this->debugStack->stopQuery();

        return $result;
    }
}