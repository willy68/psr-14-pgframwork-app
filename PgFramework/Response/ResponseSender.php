<?php

namespace PgFramework\Response;

use Psr\Http\Message\ResponseInterface;

use function function_exists;

class ResponseSender
{
    /**
     * Send an HTTP response
     *
     * @param ResponseInterface $response
     * @return void
     */
    public static function send(ResponseInterface $response): void
    {
        // headers have already sent by the developer
        if (! headers_sent()) {
            $http_line = sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            );

            header($http_line, true, $response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header("$name: $value", false);
                }
            }
        }

        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
