<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouteResult;
use PgFramework\Event\RequestEvent;
use Fig\Http\Message\RequestMethodInterface as RequestMethod;

class MethodOptionsListener
{
    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() !== RequestMethod::METHOD_OPTIONS) {
            return;
        }

        /** @var RouteResult $result */
        $result = $request->getAttribute(RouteResult::class);

        if (!$result) {
            return;
        }

        if (!$result->isMethodFailure()) {
            return;
        }

        if ($result->getMatchedRoute()) {
            return;
        }

        $event->setResponse(new Response(200, [
            'Access-Control-Allow-Headers' =>
                'X-CSRF-TOKEN,' .
                'X-Requested-With,' .
                'Content-Type,' .
                'Origin,' .
                'Authorization,' .
                'Accept,' .
                'Client-Security-Token,' .
                'User-Agent',
            'Access-Control-Allow-Methods' => $result->getAllowedMethods(),
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => 'true',
            'Content-Type' => 'application/json,application/*+json;charset=UTF-8'
        ]));
    }
}
