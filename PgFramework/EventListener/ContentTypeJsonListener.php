<?php

namespace PgFramework\EventListener;

use League\Event\ListenerPriority;
use PgFramework\Event\Events;
use PgFramework\Event\ResponseEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\HttpUtils\RequestUtils;

class ContentTypeJsonListener implements EventSubscriberInterface
{
    public function __invoke(ResponseEvent $event)
    {
        $request = $event->getRequest();
        if (RequestUtils::isJson($request)) {
            $origin = $request->getHeaderLine('origin');
            if (empty($origin)) {
                $origin = '*';
            }
            $response = $event->getResponse();
            $event->setResponse(
                $response->withAddedHeader('Content-type', 'application/json;charset=UTF-8')
                    ->withAddedHeader('Access-Control-Allow-Origin', $origin)
                    ->withAddedHeader('Cross-Origin-Embedder-Policy', 'require-corp')
                    ->withAddedHeader('Cross-Origin-Opener-Policy', 'cross-origin')
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::RESPONSE => ListenerPriority::LOW
        ];
    }
}
