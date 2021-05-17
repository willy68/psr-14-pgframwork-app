<?php

namespace PgFramework\Auth\Repository;

use PgFramework\Auth\UserInterface;

interface UserRepositoryInterface
{
    public function getUser(string $field, $value): ?UserInterface;
}
