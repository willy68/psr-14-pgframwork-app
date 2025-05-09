<?php

use PgFramework\Auth\Middleware\AuthorizationMiddleware;
use PgFramework\Auth\Middleware\ForbiddenMiddleware;
use PgFramework\Middleware\ActiveRecordMiddleware;
use PgFramework\Middleware\CsrfCookieMiddleware;
use PgFramework\Middleware\InvalidCsrfMiddleware;
use PgFramework\Middleware\RecordNotFoundMiddleware;

use function DI\add;

return [
    /**
     * Add your own router middlewares
     */
    'router.middlewares' => add([
        ForbiddenMiddleware::class,
        InvalidCsrfMiddleware::class,
        CsrfCookieMiddleware::class,
        ActiveRecordMiddleware::class,
        RecordNotFoundMiddleware::class,
        AuthorizationMiddleware::class,
    ])
];
