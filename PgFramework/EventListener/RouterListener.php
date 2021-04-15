<?php

namespace PgFramework\EventListener;

use League\Event\Listener;
use GuzzleHttp\Psr7\Response;
use PgFramework\Event\RequestEvent;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterListener implements Listener
{

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * RouterListener constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke(object $event): void
    {
        /** @var RequestEvent $event */
        /** @var ServerRequestInterface $request */
        $request = $event->getRequest();

        // Redirect if trailing slash on url
        $response = $this->trailingSlash($request);

        if (null !== $response) {
            $event->setResponse($response);
            return;
        }

        // Check http method
        $request = $this->method($request);

        $result = $this->router->match($request);

        if ($result->isFailure()) {
            $event->setRequest($request);
            return;
        }

        if ($result->isMethodFailure()) {
            $event->setRequest($request->withAttribute(get_class($result), $result));
            return;
        }

        $params = $result->getMatchedParams();
        $request = array_reduce(
            array_keys($params),
            function ($request, $key) use ($params) {
                return $request->withAttribute($key, $params[$key]);
            },
            $request
        );
        $event->setRequest($request->withAttribute(get_class($result), $result));
    }

    private function trailingSlash(ServerRequestInterface $request): ?ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        if (!empty($uri) && $uri !== '/' && $uri[strlen($uri) - 1] === '/') {
            return (new Response())
            ->withStatus(301)
            ->withHeader('Location', substr($uri, 0, -1));
        }
        return null;
    }

    private function method(ServerRequestInterface $request): ServerRequestInterface
    {
        $parseBody = $request->getParsedBody();
        if (
            is_array($parseBody) &&
            array_key_exists('_method', $parseBody) &&
            in_array($parseBody['_method'], ['DELETE', 'PUT', 'PATCH'])
        ) {
            $request = $request->withMethod($parseBody['_method']);
        }
        return $request;
    }
}
