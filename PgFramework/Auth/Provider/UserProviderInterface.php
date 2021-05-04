<?php

namespace PgFramework\Auth\Provider;

use PgFramework\Auth\User;

interface UserProviderInterface
{
    public function getUser(string $field, $value): ?User;
}
