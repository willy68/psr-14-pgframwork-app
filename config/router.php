<?php

use App\Middleware\RecordNotFoundMiddleware;
use PgFramework\Middleware\ActiveRecordMiddleware;
use PgFramework\Middleware\CsrfGetCookieMiddleware;
use PgFramework\Middleware\InvalidCsrfMiddleware;

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