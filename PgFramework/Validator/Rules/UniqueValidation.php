<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use ActiveRecord\Model;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use PDO;
use PgFramework\Validator\ValidationInterface;

class UniqueValidation implements ValidationInterface
{
    protected string $error = "Le champ %s doit être unique";

    /**
     * Table name
     */
    protected ?string $table;

    protected PDO $pdo;

    protected string $column;

    /**
     * Column value
     */
    protected string $value;

    protected ?int $exclude;

    protected ManagerRegistry $mr;

    /**
     *
     * @param PDO $pdo
     * @param ManagerRegistry $mr
     * @param string|null $table
     * @param int|null $exclude
     * @param string|null $error
     */
    public function __construct(
        PDO             $pdo,
        ManagerRegistry $mr,
        ?string         $table = null,
        ?int            $exclude = null,
        ?string         $error = null
    )
    {
        $this->pdo = $pdo;
        $this->mr = $mr;
        $this->table = $table;
        $this->exclude = $exclude;
        if (!empty($error)) {
            $this->error = $error;
        }
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public function isValid(mixed $var): bool
    {
        $query = "SELECT id FROM $this->table WHERE $this->column=?";
        $params = [$var];
        if ($this->exclude !== null) {
            $query .= " AND id != ?";
            $params[] = $this->exclude;
        }
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        if ($statement->fetchColumn() !== false) {
            $this->value = $var;
            return false;
        }
        return true;
    }

    /**
     *
     * unique:table,columnName,excludeId,errorMessage or
     * unique:App\Models\modelClass,columnName,excludeId,errorMessage
     * optionnal:excludeId and errorMessage
     * ex:unique:App\Models\Posts,slug,23,errorMessage
     *
     * @param string $param
     * @return $this
     */
    public function parseParams(string $param): self
    {
        list($tableOrModel, $column, $exclude, $message) = array_pad(explode(',', $param), 4, '');
        if (!empty($message)) {
            $this->error = $message;
        }
        if (!empty($exclude)) {
            $this->exclude = (int)$exclude;
        }
        if (empty($column)) {
            throw new InvalidArgumentException("Column name must be specified");
        }
        $this->column = $column;
        if (class_exists($tableOrModel)) {
            /** @var EntityManagerInterface $em */
            if (null !== ($em = $this->mr->getManagerForClass($tableOrModel))) {
                $this->table = $em->getClassMetadata($tableOrModel)->getTableName();
                $this->pdo = $em->getConnection()->getNativeConnection();
            } else {
                /** @var Model $tableOrModel */
                $this->table = $tableOrModel::table_name();
                /** @var PDO $pdo */
                $this->pdo = $tableOrModel::connection()->connection;
            }
        } else {
            $this->table = $tableOrModel;
        }
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getParams(): array
    {
        return [$this->column, $this->value];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
