<?php

namespace App\Models;

use ActiveRecord;

class Cpville extends ActiveRecord\Model
{
    public static $connection = 'ajax';
    public static $table_name = 'cp_autocomplete';
    public static $primary_key = 'CP';
}
