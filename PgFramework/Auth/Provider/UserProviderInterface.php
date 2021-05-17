<?php

namespace PgFramework\Auth\Provider;

use PgFramework\Auth\UserInterface;

interface UserProviderInterface
{
    public function getUser(string $field, $value): ?UserInterface;

    public function updateUser(UserInterface $user): ?UserInterface;
}
