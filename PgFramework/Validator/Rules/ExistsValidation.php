<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Validator\ValidationInterface;

class ExistsValidation implements ValidationInterface
{
    protected $error = "Le champ %s n'existe pas dans la table %s";

    /**
     * Table name
     *
     * @var string
     */
    protected $table;

    /**
     * PDO object
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * ManagerRegistry
     *
     * @var ManagerRegistry
     */
    protected $mr;

    /**
     *
     * @param string|null $table
     * @param \PDO $pdo
     * @param string|null $error
     */
    public function __construct(\PDO $pdo, ManagerRegistry $mr, ?string $table = null, ?string $error = null)
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
    public function isValid($var): bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE id=?");
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
    public function parseParams($param): self
    {
        if (is_string($param)) {
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
                    /** @var \ActiveRecord\Model $tableOrModel */
                    $this->table = $tableOrModel::table_name();
                    /** @var \PDO $pdo */
                    $this->pdo = $tableOrModel::connection()->connection;
                }
            } else {
                $this->table = $tableOrModel;
            }
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
