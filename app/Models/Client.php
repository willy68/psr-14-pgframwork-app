<?php

namespace App\Models;

use ActiveRecord;

class Client extends ActiveRecord\Model
{
    public static $table_name = 'client';
    public static $has_many = [['adresses', 'class_name' => 'Adresse']];
}
