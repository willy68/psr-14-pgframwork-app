<?php

namespace App\Models;

use ActiveRecord;

class Entreprise extends ActiveRecord\Model
{
    public static $table_name = 'entreprise';
    public static $has_many = array(
    array('administrateurs', 'class_name' => 'Administrateur'),
    array('users', 'class_name' => 'User', 'through' => 'administrateurs')
    );
}
