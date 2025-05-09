<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements MiddlewareInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->router->match($request);

        if ($result->isMethodFailure()) {
            $request = $request->withAttribute(
                get_class($result),
                $result
            );
            return $handler->handle($request);
        }
        if ($result->isFailure()) {
            return $handler->handle($request);
        }
        $params = $result->getMatchedParams();
        $request = array_reduce(
            array_keys($params),
            function ($request, $key) use ($params) {
                /** @var ServerRequestInterface $request */
                return $request->withAttribute($key, $params[$key]);
            },
            $request
        );

        /** @var ServerRequestInterface $request */
        $request = $request->withAttribute(get_class($result), $result);
        return $handler->handle($request);
    }
}
