<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouteResult;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use PgFramework\Event\Events;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class MethodNotAllowedListener implements EventSubscriberInterface
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        if (null === $routeResult) {
            return;
        }

        if ($routeResult->isMethodFailure()) {
            $event->setResponse((new Response())
                ->withStatus(StatusCode::STATUS_METHOD_NOT_ALLOWED)
                ->withHeader('Allow', implode(',', $routeResult->getAllowedMethods())));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ListenerPriority::HIGH
        ];
    }
}
