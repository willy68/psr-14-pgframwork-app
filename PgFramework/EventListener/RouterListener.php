<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PgFramework\Event\RequestEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Router\Exception\PageNotFoundException;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class RouterListener implements EventSubscriberInterface
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

    public function __invoke(RequestEvent $event): void
    {
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

        if ($result->isMethodFailure()) {
            $event->setRequest($request->withAttribute(get_class($result), $result));
            return;
        }

        if ($result->isFailure()) {
            $event->setRequest($request);
            throw new PageNotFoundException();
        }

        $params = $result->getMatchedParams();
        $request = array_reduce(
            array_keys($params),
            function ($request, $key) use ($params) {
                return $request->withAttribute($key, $params[$key]);
            },
            $request
        );
        $event->setRequest($request->withAttribute(get_class($result), $result)
                            ->withAttribute('_controller', $result->getMatchedRoute()->getCallback())
                            ->withAttribute('_params', $result->getMatchedParams()));
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

    private function redirectHttps(
        ServerRequestInterface $request,
        RouteResult $result,
        ?ResponseInterface $response = null
    ): ?ResponseInterface {
        $scheme = $request->getUri()->getScheme();
        if (! $result->getMatchedRoute()->allowsScheme($scheme)) {
            $uriClass = $request->getUri();
            $newScheme = in_array('https', $result->getMatchedRoute()->getSchemes(), true)
                ? 'https'
                : $result->getMatchedRoute()->getSchemes()[0];
            $path = $response ? $response->getHeaderLine('Location') : $uriClass->getPath();
            $uri = $uriClass
                ->withScheme($newScheme)
                ->withPath($path)
                ->__toString();
            $response = $response ?
                $response->withHeader('Location', $uri) :
                (new Response())
                    ->withStatus(301)
                    ->withHeader('Location', $uri);
        }
        return $response;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => 900
        ];
    }
}
