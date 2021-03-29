<?php

namespace App\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Framework\Renderer\RendererInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RecordNotFoundMiddleware implements MiddlewareInterface
{

    /**
     * Renderer de vue
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (RecordNotFound $e) {
            return new Response(404, [], $this->renderer->render(
                'error404',
                ['message' => $e->getMessage()]
            ));
        }
    }
}
