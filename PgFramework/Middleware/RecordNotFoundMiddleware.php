<?php

namespace PgFramework\Middleware;

use ActiveRecord\Exceptions\RecordNotFound;
use Exception;
use GuzzleHttp\Psr7\Response;
use PgFramework\Database\NoRecordException;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RecordNotFoundMiddleware implements MiddlewareInterface
{
    private RendererInterface $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Exception $e) {
            if ($e instanceof RecordNotFound || $e instanceof NoRecordException) {
                return new Response(404, [], $this->renderer->render(
                    'error404',
                    ['message' => $e->getMessage()]
                ));
            }
        }
        return $handler->handle($request);
    }
}
