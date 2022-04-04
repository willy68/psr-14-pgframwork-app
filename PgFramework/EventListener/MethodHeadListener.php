<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Utils;
use Mezzio\Router\RouterInterface;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use Psr\Http\Message\StreamInterface;
use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use League\Event\ListenerPriority;
use PgFramework\Event\Events;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class MethodHeadListener implements EventSubscriberInterface
{
    public const FORWARDED_HTTP_METHOD_ATTRIBUTE = 'forwarded_http_method';

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() !== RequestMethod::METHOD_HEAD) {
            return;
        }

        $result = $request->getAttribute(RouteResult::class);
        if (!$result) {
            return;
        }

        if ($result->getMatchedRoute()) {
            return;
        }

        $routeResult = $this->router->match($request->withMethod(RequestMethod::METHOD_GET));
        if ($routeResult->isFailure()) {
            return;
        }

        // Copy matched parameters like RouteMiddleware does
        foreach ($routeResult->getMatchedParams() as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }

        $event->setRequest(
            $request
                ->withAttribute(RouteResult::class, $routeResult)
                ->withMethod(RequestMethod::METHOD_GET)
                ->withAttribute(
                    self::FORWARDED_HTTP_METHOD_ATTRIBUTE,
                    RequestMethod::METHOD_HEAD
                )
        );
    }

    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getAttribute(self::FORWARDED_HTTP_METHOD_ATTRIBUTE)) {
            $response = $event->getResponse();

            /** @var StreamInterface $body */
            $body = Utils::streamFor(null);
            $event->setResponse($response->withBody($body));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ['onRequest', ListenerPriority::HIGH],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW]
        ];
    }
}
