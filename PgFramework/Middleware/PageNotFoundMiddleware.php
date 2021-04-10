<?php

namespace PgFramework\Middleware;

use PgFramework\Renderer\RendererInterface;
use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageNotFoundMiddleware implements MiddlewareInterface
{

    /**
     * Undocumented variable
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     * 
     *
     * @var RouterInterface
     */
    private $router;

    /**
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer, RouterInterface $router)
    {
        $this->renderer = $renderer;
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $next
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return new Response(404, [], $this->renderer->render('error404'));
    }
}
