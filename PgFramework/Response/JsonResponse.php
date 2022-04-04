<?php

declare(strict_types=1);

namespace PgFramework\Response;

use GuzzleHttp\Psr7\Response;

class JsonResponse extends Response
{
    public function __construct(
        $status = 200,
        $body = null,
        $reason = null
    ) {
        parent::__construct($status, ['content-type', 'application/json;charset=UTF-8'], $body, '1.1', $reason);
    }
}
