<?php

namespace PgFramework\EventListener;

use GuzzleHttp\Psr7\Response;
use Mezzio\Router\RouteResult;
use PgFramework\Event\RequestEvent;
use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use League\Event\ListenerPriority;
use PgFramework\Event\Events;
use PgFramework\Event\ResponseEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class MethodOptionsListener implements EventSubscriberInterface
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

        $origin = $request->getHeaderLine('origin');
        if (empty($origin)) {
            $origin = 
            $origin = $request->getUri()->getHost() . 
                ($request->getUri()->getPort() ? ':' . $request->getUri()->getPort() : '');
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
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Content-Type' => 'application/json,application/*+json;charset=UTF-8'
        ]));
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ListenerPriority::HIGH
        ];
    }
}
