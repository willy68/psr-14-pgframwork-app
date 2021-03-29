<?php

namespace App\Models;

use ActiveRecord;

class Adresse extends ActiveRecord\Model
{
    public static $table_name = 'adresse';
    public static $belongs_to = [
        [
            'client',
            'class_name' => 'Client',
            'foreign_key' => 'client_id'
        ]
    ];
}
