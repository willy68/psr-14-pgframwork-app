<?php

use App\Middleware\RecordNotFoundMiddleware;
use Framework\Middleware\ActiveRecordMiddleware;
use Framework\Middleware\CsrfGetCookieMiddleware;
use Framework\Middleware\InvalidCsrfMiddleware;

/**
 * Add your own router middlewares
 */
return [
    'router.middlewares' => \DI\add([
        InvalidCsrfMiddleware::class,
        CsrfGetCookieMiddleware::class,
        ActiveRecordMiddleware::class,
        RecordNotFoundMiddleware::class,
    ])
];