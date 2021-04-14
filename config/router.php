<?php

use App\Middleware\RecordNotFoundMiddleware;
use PgFramework\Middleware\InvalidCsrfMiddleware;
use PgFramework\Middleware\ActiveRecordMiddleware;
use PgFramework\Auth\Middleware\ForbidenMiddleware;
use PgFramework\Middleware\CsrfGetCookieMiddleware;
use PgFramework\Auth\Middleware\AuthorizationMiddleware;

/**
 * Add your own router middlewares
 */
return [
    'router.middlewares' => \DI\add([
        ForbidenMiddleware::class,
        InvalidCsrfMiddleware::class,
        CsrfGetCookieMiddleware::class,
        ActiveRecordMiddleware::class,
        RecordNotFoundMiddleware::class,
        AuthorizationMiddleware::class,
    ])
];