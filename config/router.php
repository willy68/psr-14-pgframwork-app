<?php

use App\Middleware\RecordNotFoundMiddleware;
use PgFramework\Middleware\InvalidCsrfMiddleware;
use PgFramework\Middleware\ActiveRecordMiddleware;
use PgFramework\Auth\Middleware\ForbiddenMiddleware;
use PgFramework\Middleware\CsrfCookieMiddleware;
use PgFramework\Auth\Middleware\AuthorizationMiddleware;

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
