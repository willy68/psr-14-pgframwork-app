<?php

namespace PgFramework\Middleware;

use PgFramework\Renderer\RendererInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RendererRequestMiddleware implements MiddlewareInterface
{
    private RendererInterface $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $domain = sprintf(
            '%s://%s%s',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $request->getUri()->getPort() ? ':' . $request->getUri()->getPort() : ''
        );
        $this->renderer->addGlobal('domain', $domain);
        return $handler->handle($request);
    }
}
