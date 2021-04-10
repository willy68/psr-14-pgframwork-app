<?php

namespace PgFramework\Auth\Repository;

use PgFramework\Auth\User;

interface UserRepositoryInterface
{
    public function getUser(string $field, $value): ?User;
}
