<?php

namespace PgFramework\EventListener;

use League\Event\Listener;
use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouteResult;
use PgFramework\Event\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class MethodNotAllowedListener implements Listener
{

    public function __invoke(object $event): void
    {
        /** @var RequestEvent $event */
        /** @var ServerRequestInterface $request */
        $request = $event->getRequest();

        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        if ($routeResult->isMethodFailure()) {
            $event->setResponse( (new Response())
                ->withStatus(StatusCode::STATUS_METHOD_NOT_ALLOWED)
                ->withHeader('Allow', implode(',', $routeResult->getAllowedMethods()))
            );
        }
    }
}
