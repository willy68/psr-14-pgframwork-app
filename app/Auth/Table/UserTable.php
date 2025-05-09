<?php

namespace App\Auth\Table;

use App\Auth\Entity\User;
use PgFramework\Database\Table;

class UserTable extends Table
{
    protected string $table = 'users';

    protected ?string $entity = User::class;
}
