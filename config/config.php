<?php

use Middlewares\Whoops;
use Tuupola\Http\Factory\ResponseFactory;

return [
    Whoops::class => function () {
        return new Whoops(null, new ResponseFactory());
    }
];