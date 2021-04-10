<?php

namespace App\Auth;

use App\Auth\User;
use App\Auth\Table\UserTable;
use PgFramework\Auth\User as AuthUser;
use PgFramework\Auth\Repository\UserRepositoryInterface;
use PgFramework\Database\NoRecordException;

class DatabaseUserRepository implements UserRepositoryInterface
{

    private $userTable;

    public function __construct(UserTable $userTable)
    {
        $this->userTable = $userTable;
    }

    public function getUser(string $field, $value): ?AuthUser
    {
        try {
            /** @var User $user */
            $user = $this->userTable->findBy($field, $value);
        } catch (NoRecordException $e) {
            return null;
        }
        return $user;
    }
}
