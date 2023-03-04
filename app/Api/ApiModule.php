<?php

namespace App\Api;

use App\Api\Controller\PostApiController;
use PgFramework\Module;

class ApiModule extends Module
{
    //public const DEFINITIONS = __DIR__ . '/config.php';
    public const ANNOTATIONS = [
        PostApiController::class
    ];

}
