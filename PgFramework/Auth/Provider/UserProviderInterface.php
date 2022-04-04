<?php

declare(strict_types=1);

namespace PgFramework\Auth\Provider;

use PgFramework\Auth\UserInterface;

interface UserProviderInterface
{
    public function getUser(string $field, $value): ?UserInterface;

    public function updateUser(UserInterface $user): ?UserInterface;
}
