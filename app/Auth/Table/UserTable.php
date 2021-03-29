<?php

namespace App\Auth\Table;

use App\Auth\User;
use Framework\Database\Table;

class UserTable extends Table
{

    protected $table = 'users';

    protected $entity = User::class;
}
