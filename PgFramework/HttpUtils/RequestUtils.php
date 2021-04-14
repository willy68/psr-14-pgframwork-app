<?php

namespace PgFramework\HttpUtils;

use Psr\Http\Message\ServerRequestInterface;

class RequestUtils
{

    /**
     * Not safe function
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public static function isAjax(ServerRequestInterface $request): bool
    {
        return 'XMLHttpRequest' == $request->getHeader('X-Requested-With');
    }

    /**
     * Is json request?
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public static function isJson(ServerRequestInterface $request): bool
    {
        return 1 === preg_match('{^application/(?:\w+\++)*json$}i', $request->getHeaderLine('content-type'));
    }

    /**
     * Return POST params for Ajax call or Normal parsed body
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    public static function getPostParams(ServerRequestInterface $request): array
    {
        if (static::isJson($request)) {
            $decoded = json_decode($request->getBody()->getContents(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return (array)$decoded;
            }
            return [];
        }
        return $request->getParsedBody();
    }

    /**
     *
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public static function getAcceptFormat(ServerRequestInterface $request): string
    {
        $accepts = explode(',', $request->getHeaderLine('Accept'));
        $format = 'html';
        foreach($accepts as $accept) {
            if (1 === preg_match('{^application/(?:\w+\++)*json$}i', $accept)) {
                $format = 'json';
            }
        }
        return $format;
    }
}
