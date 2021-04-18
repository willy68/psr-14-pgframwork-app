<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouteResult;
use PgFramework\Event\RequestEvent;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class MethodNotAllowedListener
{

    public function __invoke(RequestEvent $event): void
    {
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
