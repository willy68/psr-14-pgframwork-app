<?php

namespace App\Models;

use ActiveRecord;

class User extends ActiveRecord\Model
{
    public static $table_name = 'user';
    public static $has_many = array(
    array('administrateurs', 'class_name' => 'Administrateur')
    );
}
