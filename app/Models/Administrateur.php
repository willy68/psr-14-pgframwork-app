<?php

namespace App\Models;

use ActiveRecord;

class Administrateur extends ActiveRecord\Model
{
    public static $table_name = 'administrateur';
    public static $belongs_to = array(
    array('user', 'class_name' => 'User'),
    array('entreprise', 'class_name' => 'Entreprise')
    );
}
