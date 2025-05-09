<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use ActiveRecord\Model;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use PgFramework\Validator\ValidationInterface;

class ExistsValidation implements ValidationInterface
{
    protected string $error = "Le champ %s n'existe pas dans la table %s";

    /**
     * Table name
     */
    protected ?string $table;

    protected PDO $pdo;

    protected ManagerRegistry $mr;

    /**
     *
     * @param PDO $pdo
     * @param ManagerRegistry $mr
     * @param string|null $table
     * @param string|null $error
     */
    public function __construct(PDO $pdo, ManagerRegistry $mr, ?string $table = null, ?string $error = null)
    {
        $this->pdo = $pdo;
        $this->mr = $mr;
        $this->table = $table;
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
        $statement = $this->pdo->prepare("SELECT id FROM $this->table WHERE id=?");
        $statement->execute([$var]);
        if ($statement->fetchColumn() === false) {
            return false;
        }
        return true;
    }

    /**
     * exists:table,errorMessage or
     * exists:App\Models\modelClass,errorMessage
     *
     * @param string $param
     * @return $this
     */
    public function parseParams(string $param): self
    {
        list($tableOrModel, $message) = array_pad(explode(',', $param), 2, '');
        if (!empty($message)) {
            $this->error = $message;
        }
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
        return [$this->table];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
