<?php

declare(strict_types=1);

namespace PgFramework\Response;

use GuzzleHttp\Psr7\Response;

class ResponseRedirect extends Response
{
    /**
     * ResponseRedirect constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        parent::__construct(301, ['Location' => $url]);
    }
}
