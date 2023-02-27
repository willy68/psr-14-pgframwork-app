<?php

namespace App\Auth\Table;

use App\Auth\User;
use PgFramework\Database\Table;

class UserTable extends Table
{
    protected string $table = 'users';

    protected ?string $entity = User::class;
}
